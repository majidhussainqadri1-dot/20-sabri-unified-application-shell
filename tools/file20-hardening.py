#!/usr/bin/env python3
"""Apply the final fail-closed File 20 integration correction."""
from pathlib import Path

ROOT = Path("sabri-unified-application-shell")


def replace_once(text: str, old: str, new: str, label: str) -> str:
    count = text.count(old)
    if count != 1:
        raise SystemExit(f"{label}: expected one exact match, found {count}")
    return text.replace(old, new, 1)


path = ROOT / "includes/class-integrations.php"
text = path.read_text(encoding="utf-8")

text = replace_once(
    text,
    "\t\t\t'create'        => array( array( 'snp_page_map', 'publish' ) ),\n",
    "\t\t\t'create'        => array( array( 'snp_page_map', 'publish' ) ),\n\t\t\t'network'       => array( array( 'sn_page_map', 'network' ) ),\n\t\t\t'messages'      => array( array( 'sn_page_map', 'messages' ) ),\n",
    "File 17 page-map contracts",
)

old_destination = r'''		if ( in_array( $key, array( 'network', 'messages' ), true ) && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				return (string) $url;
			}
		}
'''
new_destination = r'''		if ( 'messages' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'messages_url' ) ) ) {
			$url = \SN_Activator::messages_url();
			if ( $url ) {
				return (string) $url;
			}
		}
		if ( 'network' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				return (string) $url;
			}
		}
		if ( 'messages' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				return (string) $url;
			}
		}
'''
text = replace_once(text, old_destination, new_destination, "File 17 destination resolution")

old_trusted_fallback = r'''		if ( self::is_founder( $user_id ) ) {
			return true;
		}
		if ( function_exists( 'smc_is_trusted_publisher' ) && smc_is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return (bool) get_user_meta( $user_id, '_smc_trusted_publisher', true );
'''
text = replace_once(
    text,
    old_trusted_fallback,
    "\t\treturn false;\n",
    "trusted publisher fail-closed fallback",
)

old_verified_fallback = r'''		if ( get_user_meta( $user_id, '_smc_doctor_verified', true ) ) {
			return true;
		}
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'verification_status' ) ) && 'verified' === \SPD_Helpers::verification_status( $user_id ) ) {
			return true;
		}
		$user = get_userdata( $user_id );
		return $user && (bool) array_intersect( self::verified_doctor_role_candidates(), (array) $user->roles );
'''
text = replace_once(
    text,
    old_verified_fallback,
    "\t\treturn false;\n",
    "verified doctor fail-closed fallback",
)

old_publish_fallback = r'''		if ( user_can( $user_id, 'manage_options' ) || self::is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return self::is_verified_doctor( $user_id ) && ( user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'smc_submit_knowledge' ) );
'''
text = replace_once(
    text,
    old_publish_fallback,
    "\t\treturn false;\n",
    "publishing fail-closed fallback",
)

old_profile_fallback = r'''		} else {
			$assertions = self::membership_assertions( $user_id );
			if ( ! $founder && ! empty( $assertions ) ) {
				if ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || 'doctor' !== ( $assertions['membership_type'] ?? '' ) || empty( $assertions['public_profile_allowed'] ) ) {
					return array();
				}
			}
		}
'''
new_profile_fallback = r'''		} else {
			$assertions = self::membership_assertions( $user_id );
			if ( empty( $assertions ) || ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) ) {
				return array();
			}
			if ( ! $founder && ( 'doctor' !== ( $assertions['membership_type'] ?? '' ) || empty( $assertions['public_profile_allowed'] ) ) ) {
				return array();
			}
		}
'''
text = replace_once(text, old_profile_fallback, new_profile_fallback, "public profile fail-closed fallback")

text = replace_once(
    text,
    "\t\t$contact_allowed = (bool) apply_filters( 'sabri_shell_public_contact_allowed', $contact_allowed, $user_id, $data );\n",
    "\t\t$contact_allowed = $contact_allowed && (bool) apply_filters( 'sabri_shell_public_contact_allowed', true, $user_id, $data );\n",
    "public contact deny-only filter",
)

path.write_text(text, encoding="utf-8")

run_path = ROOT / "tests/run.php"
run = run_path.read_text(encoding="utf-8")
needle = "$assert( '' === $raw_profile_contact['phone'] && '' === $raw_profile_contact['whatsapp'], 'Raw Membership profile contact never becomes public data without the File 03 consent contract.' );\n"
addition = needle + r'''

$GLOBALS['test_users'][12] = new WP_User( 12, array( 'sabri_doctor_verified' ), 'Stale Legacy Doctor' );
$GLOBALS['test_user_meta'][12]['_smc_doctor_verified'] = 1;
$GLOBALS['test_user_meta'][12]['_smc_trusted_publisher'] = 1;
$GLOBALS['test_user_caps'][12]['edit_posts'] = true;
$GLOBALS['test_membership_assertions'][12] = array();
$GLOBALS['test_directory_eligible'][12] = false;
$assert( ! Integrations::is_verified_doctor( 12 ), 'Legacy verified metadata cannot substitute for File 00 and File 03 authority.' );
$assert( ! Integrations::is_trusted_publisher( 12 ), 'Legacy trusted-publisher metadata cannot substitute for current File 00 assertions.' );
$assert( ! Integrations::can_publish( 12 ), 'Composer access fails closed when the identity authority is unavailable.' );
$assert( array() === Integrations::doctor_public_data( 12 ), 'Public doctor data fails closed when both approved File 03 projection and valid File 00 assertions are unavailable.' );
'''
run = replace_once(run, needle, addition, "fail-closed contract tests")
run_path.write_text(run, encoding="utf-8")

review_path = ROOT / "REVIEW-CORRECTIONS.md"
review = review_path.read_text(encoding="utf-8")
review = replace_once(
    review,
    "| Stale role/meta authority and public-contact leakage | File 00 current assertions govern publishing; File 03 governs public doctor eligibility and explicit phone/WhatsApp consent. |\n",
    "| Stale role/meta authority and public-contact leakage | File 00 current assertions govern publishing; File 03 governs public doctor eligibility and explicit phone/WhatsApp consent. Legacy role/meta fallbacks now fail closed. |\n| Combined Network/Messages destination | File 20 prefers a dedicated File 17 `messages_url()`/page-map contract and uses the shared Network URL only as a compatibility fallback. |\n",
    "final review traceability rows",
)
review = replace_once(
    review,
    "- adversarial File 00 suspension/2FA and File 03 profile/contact-consent regression matrix;\n",
    "- adversarial File 00 suspension/2FA, missing-authority fail-closed, and File 03 profile/contact-consent regression matrix;\n",
    "final review validation text",
)
review_path.write_text(review, encoding="utf-8")

print("File 20 final fail-closed integration correction applied.")
