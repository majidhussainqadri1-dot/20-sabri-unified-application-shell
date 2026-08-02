from pathlib import Path


def replace_once(path: Path, old: str, new: str) -> None:
    text = path.read_text(encoding="utf-8")
    if new in text:
        return
    if old not in text:
        raise SystemExit(f"Expected fragment not found in {path}: {old[:80]!r}")
    path.write_text(text.replace(old, new, 1), encoding="utf-8")


root = Path("sabri-unified-application-shell")
integrations = root / "includes/class-integrations.php"
bootstrap = root / "tests/bootstrap.php"
run = root / "tests/run.php"

replace_once(
    integrations,
    """\t\treturn $assertions;\n\t}\n\n\t/**\n\t * Whether a user is the authoritative Founder identity.\n""",
    """\t\treturn $assertions;\n\t}\n\n\t/** Whether File 00 has placed the exact subject in a terminal/restricted state. */\n\tprivate static function assertions_have_hard_block( array $assertions ) {\n\t\treturn ! empty( $assertions['_contract_error'] )\n\t\t\t|| ! empty( $assertions['suspended'] )\n\t\t\t|| in_array(\n\t\t\t\t(string) ( $assertions['status'] ?? '' ),\n\t\t\t\tarray( 'rejected', 'suspended', 'expired', 'appeal_review', 'erasure_pending', 'invalid_application' ),\n\t\t\t\ttrue\n\t\t\t);\n\t}\n\n\t/** Whether File 00 permits a current public identity projection. */\n\tprivate static function assertions_allow_public_identity( array $assertions ) {\n\t\treturn ! self::assertions_have_hard_block( $assertions )\n\t\t\t&& ! empty( $assertions['approved'] )\n\t\t\t&& ! empty( $assertions['eligible'] )\n\t\t\t&& ! empty( $assertions['identity_evidence_current'] );\n\t}\n\n\t/** Whether File 00 permits a privileged action in the current session. */\n\tprivate static function assertions_allow_privileged_action( array $assertions ) {\n\t\treturn self::assertions_allow_public_identity( $assertions )\n\t\t\t&& ! empty( $assertions['two_factor_ready'] )\n\t\t\t&& ! empty( $assertions['session_two_factor'] )\n\t\t\t&& ! empty( $assertions['sensitive_action_ready'] );\n\t}\n\n\t/**\n\t * Whether a user is the authoritative Founder identity.\n""",
)

replace_once(
    integrations,
    """\t\t$assertions = self::membership_assertions( $user_id );\n\t\treturn empty( $assertions['_contract_error'] )\n\t\t\t&& 'founder' === ( $assertions['account_class'] ?? '' )\n\t\t\t&& ! empty( $assertions['approved'] )\n\t\t\t&& empty( $assertions['suspended'] )\n\t\t\t&& ! in_array( (string) ( $assertions['status'] ?? '' ), array( 'rejected', 'suspended', 'expired', 'appeal_review', 'erasure_pending', 'invalid_application' ), true );\n""",
    """\t\t$assertions = self::membership_assertions( $user_id );\n\t\treturn self::assertions_allow_public_identity( $assertions )\n\t\t\t&& 'founder' === ( $assertions['account_class'] ?? '' );\n""",
)

old_action_gate = """\t\tif ( ! empty( $assertions ) ) {\n\t\t\tif ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || empty( $assertions['eligible'] ) || empty( $assertions['session_two_factor'] ) ) {\n\t\t\t\treturn false;\n\t\t\t}\n"""
new_action_gate = """\t\tif ( ! empty( $assertions ) ) {\n\t\t\tif ( ! self::assertions_allow_privileged_action( $assertions ) ) {\n\t\t\t\treturn false;\n\t\t\t}\n"""
for _ in range(2):
    replace_once(integrations, old_action_gate, new_action_gate)

replace_once(
    integrations,
    """\t\t$assertions = self::membership_assertions( $user_id );\n\t\tif ( ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) || empty( $assertions['eligible'] ) || empty( $assertions['professional_verified'] ) ) {\n\t\t\treturn false;\n\t\t}\n""",
    """\t\t$assertions = self::membership_assertions( $user_id );\n\t\tif ( ! self::assertions_allow_public_identity( $assertions ) || empty( $assertions['professional_verified'] ) ) {\n\t\t\treturn false;\n\t\t}\n""",
)

replace_once(
    integrations,
    """\t\t} else {\n\t\t\t$assertions = self::membership_assertions( $user_id );\n\t\t\tif ( empty( $assertions ) || ! empty( $assertions['_contract_error'] ) || ! empty( $assertions['suspended'] ) || empty( $assertions['approved'] ) ) {\n\t\t\t\treturn array();\n\t\t\t}\n""",
    """\t\t} else {\n\t\t\t$assertions = self::membership_assertions( $user_id );\n\t\t\tif ( ! self::assertions_allow_public_identity( $assertions ) ) {\n\t\t\t\treturn array();\n\t\t\t}\n""",
)

replace_once(
    integrations,
    """\t\t$filtered = apply_filters( 'sabri_shell_doctor_public_data', $data, $user_id );\n\t\tif ( is_array( $filtered ) ) {\n\t\t\t$data = $filtered;\n\t\t}\n\t\tif ( ! $contact_allowed ) {\n\t\t\t$data['phone']    = '';\n\t\t\t$data['whatsapp'] = '';\n\t\t}\n\t\tforeach ( $data as $key => $value ) {\n\t\t\t$data[ $key ] = is_scalar( $value ) ? (string) $value : '';\n\t\t}\n\t\treturn $data;\n""",
    """\t\t$filtered = apply_filters( 'sabri_shell_doctor_public_data', $data, $user_id );\n\t\tif ( is_array( $filtered ) ) {\n\t\t\t// Extension callbacks may refine approved fields but cannot add a new\n\t\t\t// undeclared public-data channel (for example email or identity data).\n\t\t\t$data = array_merge( $data, array_intersect_key( $filtered, $data ) );\n\t\t}\n\t\tif ( ! $contact_allowed ) {\n\t\t\t$data['phone']    = '';\n\t\t\t$data['whatsapp'] = '';\n\t\t}\n\t\tforeach ( $data as $key => $value ) {\n\t\t\t$value = is_scalar( $value ) ? (string) $value : '';\n\t\t\t$data[ $key ] = 'profile' === $key\n\t\t\t\t? ( function_exists( 'esc_url_raw' ) ? esc_url_raw( $value ) : $value )\n\t\t\t\t: ( function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $value ) : strip_tags( $value ) );\n\t\t}\n\t\treturn $data;\n""",
)

replace_once(
    bootstrap,
    "$GLOBALS['test_profiles'] = array();\n\nfunction __( $text ) { return $text; }\n",
    "$GLOBALS['test_profiles'] = array();\n$GLOBALS['test_filter_overrides'] = array();\n\nfunction __( $text ) { return $text; }\n",
)
replace_once(
    bootstrap,
    "function apply_filters( $tag, $value ) { return $value; }\n",
    """function apply_filters( $tag, $value, ...$args ) {\n\tif ( array_key_exists( $tag, $GLOBALS['test_filter_overrides'] ) ) {\n\t\t$override = $GLOBALS['test_filter_overrides'][ $tag ];\n\t\treturn is_callable( $override ) ? $override( $value, ...$args ) : $override;\n\t}\n\treturn $value;\n}\n""",
)

replace_once(
    run,
    """\t'approved'              => true,\n\t'eligible'              => true,\n\t'session_two_factor'    => true,\n""",
    """\t'approved'              => true,\n\t'eligible'              => true,\n\t'identity_evidence_current' => true,\n\t'two_factor_ready'      => true,\n\t'session_two_factor'    => true,\n\t'sensitive_action_ready'=> true,\n""",
)
replace_once(
    run,
    """\t'approved'              => false,\n\t'eligible'              => false,\n\t'session_two_factor'    => false,\n""",
    """\t'approved'              => false,\n\t'eligible'              => false,\n\t'identity_evidence_current' => false,\n\t'two_factor_ready'      => false,\n\t'session_two_factor'    => false,\n\t'sensitive_action_ready'=> false,\n""",
)
old_institution = """\t'approved'           => true,\n\t'eligible'           => true,\n\t'session_two_factor' => true,\n"""
new_institution = """\t'approved'           => true,\n\t'eligible'           => true,\n\t'identity_evidence_current' => true,\n\t'two_factor_ready'   => true,\n\t'session_two_factor' => true,\n\t'sensitive_action_ready' => true,\n"""
for _ in range(2):
    replace_once(run, old_institution, new_institution)

replace_once(
    run,
    "$assert( '+923001234567' === $public_contact['phone'] && '+923001234567' === $public_contact['whatsapp'], 'Approved contact fields render only after explicit File 03 public-contact consent.' );\n\n$GLOBALS['test_users'][8] = new WP_User",
    """$assert( '+923001234567' === $public_contact['phone'] && '+923001234567' === $public_contact['whatsapp'], 'Approved contact fields render only after explicit File 03 public-contact consent.' );\n$GLOBALS['test_filter_overrides']['sabri_shell_doctor_public_data'] = static function ( $data ) {\n\t$data['email'] = 'private@example.test';\n\t$data['country'] = '<b>Pakistan</b>';\n\treturn $data;\n};\n$filtered_public = Integrations::doctor_public_data( 7 );\n$assert( ! array_key_exists( 'email', $filtered_public ), 'Public doctor projection filters cannot introduce undeclared private fields.' );\n$assert( 'Pakistan' === $filtered_public['country'], 'Public doctor projection filter values are sanitized.' );\nunset( $GLOBALS['test_filter_overrides']['sabri_shell_doctor_public_data'] );\n\n$GLOBALS['test_users'][8] = new WP_User""",
)

replace_once(
    run,
    "$assert( Integrations::can_publish( 10 ), 'A valid institutional Administrator may reach the composer without bypassing File 00 status and 2FA.' );\n\n$GLOBALS['test_users'][11]",
    """$assert( Integrations::can_publish( 10 ), 'A valid institutional Administrator may reach the composer without bypassing File 00 status and 2FA.' );\n\n$GLOBALS['test_users'][13] = new WP_User( 13, array( 'founder' ), 'Expired Founder' );\n$GLOBALS['test_membership_assertions'][13] = $GLOBALS['test_membership_assertions'][9];\n$GLOBALS['test_membership_assertions'][13]['user_id'] = 13;\n$GLOBALS['test_membership_assertions'][13]['status'] = 'expired';\n$assert( ! Integrations::can_publish( 13 ), 'Terminal File 00 status blocks publishing even when stale allow booleans remain true.' );\n$GLOBALS['test_users'][14] = new WP_User( 14, array( 'founder' ), 'Stale Session Founder' );\n$GLOBALS['test_membership_assertions'][14] = $GLOBALS['test_membership_assertions'][9];\n$GLOBALS['test_membership_assertions'][14]['user_id'] = 14;\n$GLOBALS['test_membership_assertions'][14]['sensitive_action_ready'] = false;\n$assert( ! Integrations::can_publish( 14 ), 'Missing File 00 sensitive-action assurance blocks composer access.' );\n\n$GLOBALS['test_users'][11]""",
)

print("File 20 round-three source and regression updates applied.")
