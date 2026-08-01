#!/usr/bin/env python3
"""Apply the second-round File 20 authority/privacy correction deterministically."""
from pathlib import Path
import re

ROOT = Path("sabri-unified-application-shell")


def replace_once(text: str, old: str, new: str, label: str) -> str:
    count = text.count(old)
    if count != 1:
        raise SystemExit(f"{label}: expected one exact match, found {count}")
    return text.replace(old, new, 1)


integrations_path = ROOT / "includes/class-integrations.php"
integrations = integrations_path.read_text(encoding="utf-8")
pattern = re.compile(
    r"\t/\*\*\n\t \* Whether a user is the authoritative Founder\..*?(?=\n\t/\*\*\n\t \* Render a real language switcher)",
    re.S,
)
new_authority_section = r'''	/**
	 * Return File 00 membership assertions without duplicating identity state.
	 *
	 * @param int $user_id User ID.
	 * @return array<string,mixed>
	 */
	public static function membership_assertions( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return array();
		}

		if ( class_exists( 'SMC_Contracts' ) && is_callable( array( 'SMC_Contracts', 'assertions' ) ) ) {
			try {
				$assertions = \SMC_Contracts::assertions( $user_id );
				return is_array( $assertions ) ? $assertions : array( '_contract_error' => true );
			} catch ( \Throwable $error ) {
				unset( $error );
				return array( '_contract_error' => true );
			}
		}

		$assertions = apply_filters( 'smc_assertions_v1', array(), $user_id );
		return is_array( $assertions ) ? $assertions : array();
	}

	/**
	 * Whether a user is the authoritative Founder identity.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_founder( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) && 'founder' === ( $assertions['account_class'] ?? '' ) ) {
			return true;
		}
		if ( function_exists( 'smc_is_founder' ) && smc_is_founder( $user_id ) ) {
			return true;
		}
		if ( get_user_meta( $user_id, '_smc_official_founder', true ) ) {
			return true;
		}
		return $user_id === absint( get_option( 'spf_founder_user_id', 0 ) );
	}

	/**
	 * Whether File 00 currently grants trusted publishing authority.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_trusted_publisher( $user_id ) {
		$user_id    = absint( $user_id );
		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) ) {
			if ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || empty( $assertions['eligible'] ) || empty( $assertions['session_two_factor'] ) ) {
				return false;
			}
			if ( ! empty( $assertions['can_publish'] ) ) {
				return true;
			}
			if ( 'founder' === ( $assertions['account_class'] ?? '' ) ) {
				return true;
			}
			return 'administrator' === ( $assertions['account_class'] ?? '' ) && user_can( $user_id, 'manage_options' );
		}

		if ( self::is_founder( $user_id ) ) {
			return true;
		}
		if ( function_exists( 'smc_is_trusted_publisher' ) && smc_is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return (bool) get_user_meta( $user_id, '_smc_trusted_publisher', true );
	}

	/**
	 * Whether a doctor is verified and eligible for public shell discovery.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_verified_doctor( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		if ( class_exists( 'SPD_Verification_Adapter' ) && is_callable( array( 'SPD_Verification_Adapter', 'directory_eligible' ) ) ) {
			return (bool) \SPD_Verification_Adapter::directory_eligible( $user_id );
		}

		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) ) {
			if ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || empty( $assertions['eligible'] ) || empty( $assertions['professional_verified'] ) ) {
				return false;
			}
			if ( array_key_exists( 'public_profile_allowed', $assertions ) && empty( $assertions['public_profile_allowed'] ) ) {
				return false;
			}
			return 'doctor' === ( $assertions['membership_type'] ?? '' ) && ( ! empty( $assertions['can_practice'] ) || ! empty( $assertions['can_publish'] ) );
		}

		if ( get_user_meta( $user_id, '_smc_doctor_verified', true ) ) {
			return true;
		}
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'verification_status' ) ) && 'verified' === \SPD_Helpers::verification_status( $user_id ) ) {
			return true;
		}
		$user = get_userdata( $user_id );
		return $user && (bool) array_intersect( self::verified_doctor_role_candidates(), (array) $user->roles );
	}

	/**
	 * Whether a user may reach the platform composer.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function can_publish( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}

		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) ) {
			if ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || empty( $assertions['eligible'] ) || empty( $assertions['session_two_factor'] ) ) {
				return false;
			}
			if ( ! empty( $assertions['can_publish'] ) ) {
				return true;
			}
			if ( 'founder' === ( $assertions['account_class'] ?? '' ) ) {
				return true;
			}
			return 'administrator' === ( $assertions['account_class'] ?? '' ) && user_can( $user_id, 'manage_options' );
		}

		if ( user_can( $user_id, 'manage_options' ) || self::is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return self::is_verified_doctor( $user_id ) && ( user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'smc_submit_knowledge' ) );
	}

	/**
	 * Public doctor data from File 03 approved projections only.
	 *
	 * @param int $user_id User ID.
	 * @return array<string,string>
	 */
	public static function doctor_public_data( $user_id ) {
		$user_id = absint( $user_id );
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}

		$founder          = self::is_founder( $user_id );
		$approved_fields  = array();
		$file03_available = class_exists( 'SPD_Verification_Adapter' ) && is_callable( array( 'SPD_Verification_Adapter', 'directory_eligible' ) );

		if ( $file03_available ) {
			if ( ! $founder && ! \SPD_Verification_Adapter::directory_eligible( $user_id ) ) {
				return array();
			}
			if ( is_callable( array( 'SPD_Verification_Adapter', 'approved_fields' ) ) ) {
				$approved_fields = \SPD_Verification_Adapter::approved_fields( $user_id );
				$approved_fields = is_array( $approved_fields ) ? $approved_fields : array();
			}
		} else {
			$assertions = self::membership_assertions( $user_id );
			if ( ! $founder && ! empty( $assertions ) ) {
				if ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || 'doctor' !== ( $assertions['membership_type'] ?? '' ) || empty( $assertions['public_profile_allowed'] ) ) {
					return array();
				}
			}
		}

		$data = array(
			'name'      => (string) ( $approved_fields['display_name'] ?? $user->display_name ),
			'profile'   => self::profile_url( $user_id ),
			'country'   => (string) ( $approved_fields['country'] ?? '' ),
			'city'      => (string) ( $approved_fields['city'] ?? '' ),
			'clinic'    => (string) ( $approved_fields['clinic'] ?? '' ),
			'fee'       => (string) ( $approved_fields['fee'] ?? '' ),
			'currency'  => (string) ( $approved_fields['currency'] ?? '' ),
			'timings'   => (string) ( $approved_fields['timings'] ?? ( $approved_fields['hours'] ?? '' ) ),
			'languages' => (string) ( $approved_fields['languages'] ?? '' ),
			'specialty' => (string) ( $approved_fields['specialty'] ?? ( $approved_fields['specialization'] ?? '' ) ),
			'phone'     => '',
			'whatsapp'  => '',
		);

		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'get' ) ) ) {
			foreach ( array( 'country', 'city', 'clinic', 'languages', 'specialty' ) as $field ) {
				if ( empty( $data[ $field ] ) ) {
					$data[ $field ] = (string) \SPD_Helpers::get( $user_id, $field );
				}
			}
		}

		if ( function_exists( 'smc_get_profile' ) ) {
			$profile = smc_get_profile( $user_id );
			if ( is_array( $profile ) ) {
				foreach ( array( 'country', 'city' ) as $field ) {
					if ( empty( $data[ $field ] ) && ! empty( $profile[ $field ] ) ) {
						$data[ $field ] = (string) $profile[ $field ];
					}
				}
				if ( empty( $data['languages'] ) && ! empty( $profile['preferred_language'] ) ) {
					$data['languages'] = (string) $profile['preferred_language'];
				}
			}
		}

		$contact_allowed = false;
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'can_show_contact' ) ) ) {
			$contact_allowed = (bool) \SPD_Helpers::can_show_contact( $user_id, $founder );
		}
		$contact_allowed = (bool) apply_filters( 'sabri_shell_public_contact_allowed', $contact_allowed, $user_id, $data );
		if ( $contact_allowed ) {
			$data['phone']    = (string) ( $approved_fields['phone'] ?? '' );
			$data['whatsapp'] = (string) ( $approved_fields['whatsapp'] ?? '' );
			if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'get' ) ) ) {
				$data['phone']    = $data['phone'] ?: (string) \SPD_Helpers::get( $user_id, 'phone' );
				$data['whatsapp'] = $data['whatsapp'] ?: (string) \SPD_Helpers::get( $user_id, 'whatsapp' );
			}
		}

		$filtered = apply_filters( 'sabri_shell_doctor_public_data', $data, $user_id );
		if ( is_array( $filtered ) ) {
			$data = $filtered;
		}
		if ( ! $contact_allowed ) {
			$data['phone']    = '';
			$data['whatsapp'] = '';
		}
		foreach ( $data as $key => $value ) {
			$data[ $key ] = is_scalar( $value ) ? (string) $value : '';
		}
		return $data;
	}
'''
updated, count = pattern.subn(new_authority_section, integrations, count=1)
if count != 1:
    raise SystemExit(f"class-integrations authority section: expected one match, found {count}")
integrations_path.write_text(updated, encoding="utf-8")

bootstrap_path = ROOT / "tests/bootstrap.php"
bootstrap = bootstrap_path.read_text(encoding="utf-8")
bootstrap = replace_once(
    bootstrap,
    "$GLOBALS['test_users'] = array();\n",
    "$GLOBALS['test_users'] = array();\n$GLOBALS['test_user_caps'] = array();\n$GLOBALS['test_membership_assertions'] = array();\n$GLOBALS['test_directory_eligible'] = array();\n$GLOBALS['test_approved_fields'] = array();\n$GLOBALS['test_public_contact'] = array();\n$GLOBALS['test_profiles'] = array();\n",
    "bootstrap globals",
)
bootstrap = replace_once(
    bootstrap,
    "function user_can() { return false; }\n",
    "function user_can( $user, $cap = '' ) { $id = $user instanceof WP_User ? $user->ID : absint( $user ); return ! empty( $GLOBALS['test_user_caps'][ $id ][ $cap ] ); }\n",
    "bootstrap user_can",
)
stubs = r'''
class SMC_Contracts {
	public static function assertions( $user_id ) {
		return $GLOBALS['test_membership_assertions'][ absint( $user_id ) ] ?? array();
	}
}
class SPD_Verification_Adapter {
	public static function directory_eligible( $user_id ) {
		return ! empty( $GLOBALS['test_directory_eligible'][ absint( $user_id ) ] );
	}
	public static function approved_fields( $user_id ) {
		return $GLOBALS['test_approved_fields'][ absint( $user_id ) ] ?? array();
	}
}
class SPD_Helpers {
	public static function get( $user_id, $key, $default = '' ) {
		$fields = $GLOBALS['test_approved_fields'][ absint( $user_id ) ] ?? array();
		if ( array_key_exists( $key, $fields ) ) {
			return $fields[ $key ];
		}
		$profile = $GLOBALS['test_profiles'][ absint( $user_id ) ] ?? array();
		return array_key_exists( $key, $profile ) ? $profile[ $key ] : $default;
	}
	public static function can_show_contact( $user_id, $founder = false ) {
		return ! empty( $GLOBALS['test_public_contact'][ absint( $user_id ) ] ) || ( $founder && smc_is_founder( $user_id ) );
	}
	public static function profile_url( $user_id ) {
		return 'https://example.test/profile/?user=' . absint( $user_id );
	}
	public static function verification_status( $user_id ) {
		return ! empty( $GLOBALS['test_directory_eligible'][ absint( $user_id ) ] ) ? 'verified' : 'pending';
	}
}
function smc_get_profile( $user_id ) { return $GLOBALS['test_profiles'][ absint( $user_id ) ] ?? array(); }
function smc_is_founder( $user_id ) { return 'founder' === ( $GLOBALS['test_membership_assertions'][ absint( $user_id ) ]['account_class'] ?? '' ); }

'''
bootstrap = replace_once(bootstrap, "class WP_User {\n", stubs + "class WP_User {\n", "bootstrap authority stubs")
bootstrap_path.write_text(bootstrap, encoding="utf-8")

run_path = ROOT / "tests/run.php"
run = run_path.read_text(encoding="utf-8")
old_test = r'''$GLOBALS['test_users'][7] = new WP_User( 7, array( 'sabri_doctor' ), 'Verified Doctor' );
$GLOBALS['test_user_meta'][7]['_smc_doctor_verified'] = 1;
$assert( Integrations::is_verified_doctor( 7 ), 'Membership Core verification metadata is honored.' );
'''
new_test = r'''$GLOBALS['test_users'][7] = new WP_User( 7, array( 'sabri_doctor_verified' ), 'Verified Doctor' );
$GLOBALS['test_user_meta'][7]['_smc_doctor_verified'] = 1;
$GLOBALS['test_membership_assertions'][7] = array(
	'contract_version'      => '1.1.1',
	'account_class'         => 'member',
	'membership_type'       => 'doctor',
	'approved'              => true,
	'eligible'              => true,
	'session_two_factor'    => true,
	'professional_verified' => true,
	'can_publish'           => true,
	'can_practice'          => true,
	'public_profile_allowed'=> true,
	'suspended'             => false,
);
$GLOBALS['test_directory_eligible'][7] = true;
$GLOBALS['test_approved_fields'][7] = array(
	'display_name' => 'Verified Doctor',
	'country'      => 'Pakistan',
	'specialty'    => 'Classical Homeopathy',
	'phone'        => '+923001234567',
	'whatsapp'     => '+923001234567',
);
$GLOBALS['test_public_contact'][7] = false;
$assert( Integrations::is_verified_doctor( 7 ), 'File 03 directory eligibility governs public verified-doctor discovery.' );
$assert( Integrations::can_publish( 7 ), 'File 00 current-session publishing assertion authorizes a verified doctor.' );
$private_contact = Integrations::doctor_public_data( 7 );
$assert( '' === $private_contact['phone'] && '' === $private_contact['whatsapp'], 'Doctor phone and WhatsApp remain hidden without explicit File 03 public-contact consent.' );
$GLOBALS['test_public_contact'][7] = true;
$public_contact = Integrations::doctor_public_data( 7 );
$assert( '+923001234567' === $public_contact['phone'] && '+923001234567' === $public_contact['whatsapp'], 'Approved contact fields render only after explicit File 03 public-contact consent.' );

$GLOBALS['test_users'][8] = new WP_User( 8, array( 'sabri_doctor_verified' ), 'Suspended Doctor' );
$GLOBALS['test_user_meta'][8]['_smc_doctor_verified'] = 1;
$GLOBALS['test_membership_assertions'][8] = array(
	'contract_version'      => '1.1.1',
	'account_class'         => 'member',
	'membership_type'       => 'doctor',
	'approved'              => false,
	'eligible'              => false,
	'session_two_factor'    => false,
	'professional_verified' => true,
	'can_publish'           => false,
	'can_practice'          => false,
	'public_profile_allowed'=> false,
	'suspended'             => true,
);
$GLOBALS['test_directory_eligible'][8] = false;
$assert( ! Integrations::is_verified_doctor( 8 ), 'A stale verified meta flag cannot expose a suspended doctor.' );
$assert( ! Integrations::can_publish( 8 ), 'A stale trusted role or capability cannot bypass File 00 suspension.' );
$assert( array() === Integrations::doctor_public_data( 8 ), 'A suspended/private doctor produces no public profile projection.' );

$GLOBALS['test_users'][9] = new WP_User( 9, array( 'founder' ), 'Founder' );
$GLOBALS['test_membership_assertions'][9] = array(
	'contract_version'   => '1.1.1',
	'account_class'      => 'founder',
	'approved'           => true,
	'eligible'           => true,
	'session_two_factor' => true,
	'can_publish'        => true,
	'suspended'          => false,
);
$assert( Integrations::can_publish( 9 ), 'Canonical Founder publishing remains authorized only through current File 00 assertions.' );

$GLOBALS['test_users'][10] = new WP_User( 10, array( 'administrator' ), 'Administrator' );
$GLOBALS['test_user_caps'][10]['manage_options'] = true;
$GLOBALS['test_membership_assertions'][10] = array(
	'contract_version'   => '1.1.1',
	'account_class'      => 'administrator',
	'institutional_account' => true,
	'approved'           => true,
	'eligible'           => true,
	'session_two_factor' => true,
	'can_publish'        => false,
	'suspended'          => false,
);
$assert( Integrations::can_publish( 10 ), 'A valid institutional Administrator may reach the composer without bypassing File 00 status and 2FA.' );

$GLOBALS['test_users'][11] = new WP_User( 11, array( 'sabri_doctor_verified' ), 'Private Contact Doctor' );
$GLOBALS['test_membership_assertions'][11] = $GLOBALS['test_membership_assertions'][7];
$GLOBALS['test_directory_eligible'][11] = true;
$GLOBALS['test_approved_fields'][11] = array( 'display_name' => 'Private Contact Doctor' );
$GLOBALS['test_profiles'][11] = array( 'phone' => '+923009999999', 'whatsapp' => '+923009999999', 'country' => 'Pakistan' );
$GLOBALS['test_public_contact'][11] = false;
$raw_profile_contact = Integrations::doctor_public_data( 11 );
$assert( '' === $raw_profile_contact['phone'] && '' === $raw_profile_contact['whatsapp'], 'Raw Membership profile contact never becomes public data without the File 03 consent contract.' );
'''
run = replace_once(run, old_test, new_test, "contract test expansion")
run_path.write_text(run, encoding="utf-8")

review_path = ROOT / "REVIEW-CORRECTIONS.md"
review = review_path.read_text(encoding="utf-8")
review = replace_once(
    review,
    "| Wrong doctor/clinic data model | Reads existing profile helpers and Membership Core public professional/approved-clinic data. |\n",
    "| Wrong doctor/clinic data model | Consumes File 03 approved projection and directory-eligibility contracts; direct queries to non-owned Membership Core tables are removed. |\n| Stale role/meta authority and public-contact leakage | File 00 current assertions govern publishing; File 03 governs public doctor eligibility and explicit phone/WhatsApp consent. |\n",
    "review traceability rows",
)
review = replace_once(
    review,
    "- behavioral regression suite in `tests/run.php`;\n",
    "- behavioral regression suite in `tests/run.php`;\n- adversarial File 00 suspension/2FA and File 03 profile/contact-consent regression matrix;\n- static proof that File 20 contains no direct queries to File 00-owned or nonexistent professional/clinic tables;\n",
    "review validation bullets",
)
review_path.write_text(review, encoding="utf-8")

print("File 20 second-round authority/privacy hardening applied.")
