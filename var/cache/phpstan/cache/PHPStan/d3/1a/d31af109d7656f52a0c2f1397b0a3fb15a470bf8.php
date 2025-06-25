<?php declare(strict_types = 1);

// odsl-/Users/fred/PhpstormProjects/mcp_manager/tests
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/ExampleTest.php' => 
    array (
      0 => 'fc8106358d932d0e2dc66f70f07a1513b7420ae1',
      1 => 
      array (
        0 => 'tests\\unit\\exampletest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\test_that_true_is_true',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Models/IntegrationAccountTest.php' => 
    array (
      0 => '4d3c94971c0dfa195c9f51c941c73e356d54d861',
      1 => 
      array (
        0 => 'tests\\unit\\models\\integrationaccounttest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\models\\test_integration_account_belongs_to_user',
        1 => 'tests\\unit\\models\\test_integration_account_casts_type_to_enum',
        2 => 'tests\\unit\\models\\test_integration_account_casts_status_to_enum',
        3 => 'tests\\unit\\models\\test_integration_account_casts_meta_to_array',
        4 => 'tests\\unit\\models\\test_user_can_have_multiple_integration_accounts',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/StringUtilsTest.php' => 
    array (
      0 => '2c00f2119126aa58b3774842cd99f7b3039d1dab',
      1 => 
      array (
        0 => 'tests\\unit\\stringutilstest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\test_string_reversal',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Services/NotionServiceTest.php' => 
    array (
      0 => 'd6b607e3525315f35efb4554153f3fd4de0a67be',
      1 => 
      array (
        0 => 'tests\\unit\\services\\notionservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\setup',
        1 => 'tests\\unit\\services\\test_fetch_notion_pages_successful_with_default_page_id',
        2 => 'tests\\unit\\services\\test_fetch_notion_pages_successful_with_explicit_page_id',
        3 => 'tests\\unit\\services\\test_fetch_notion_pages_throws_exception_when_config_missing',
        4 => 'tests\\unit\\services\\test_fetch_notion_pages_throws_exception_when_token_missing',
        5 => 'tests\\unit\\services\\test_fetch_notion_pages_throws_exception_when_request_fails',
        6 => 'tests\\unit\\services\\test_fetch_notion_pages_successful_without_page_id',
        7 => 'tests\\unit\\services\\test_fetch_notion_pages_with_integration_account',
        8 => 'tests\\unit\\services\\test_fetch_notion_databases',
        9 => 'tests\\unit\\services\\test_fetch_notion_page',
        10 => 'tests\\unit\\services\\test_fetch_notion_blocks',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Settings/PasswordUpdateTest.php' => 
    array (
      0 => '3674e7ce8ae5bde7d8498905b66631378f2bb145',
      1 => 
      array (
        0 => 'tests\\feature\\settings\\passwordupdatetest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\settings\\test_password_can_be_updated',
        1 => 'tests\\feature\\settings\\test_correct_password_must_be_provided_to_update_password',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Settings/ProfileUpdateTest.php' => 
    array (
      0 => '1b4ca326ef9fa66f76b124881a76a6589e84b11a',
      1 => 
      array (
        0 => 'tests\\feature\\settings\\profileupdatetest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\settings\\test_profile_page_is_displayed',
        1 => 'tests\\feature\\settings\\test_profile_information_can_be_updated',
        2 => 'tests\\feature\\settings\\test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged',
        3 => 'tests\\feature\\settings\\test_user_can_delete_their_account',
        4 => 'tests\\feature\\settings\\test_correct_password_must_be_provided_to_delete_account',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/PasswordConfirmationTest.php' => 
    array (
      0 => '3d1f8a8685b1aa7df2607b16f3f7d57ec4747244',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\passwordconfirmationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\test_confirm_password_screen_can_be_rendered',
        1 => 'tests\\feature\\auth\\test_password_can_be_confirmed',
        2 => 'tests\\feature\\auth\\test_password_is_not_confirmed_with_invalid_password',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/EmailVerificationTest.php' => 
    array (
      0 => '52ab0195c3ce6089cbea9751454943220af6f767',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\emailverificationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\test_email_verification_screen_can_be_rendered',
        1 => 'tests\\feature\\auth\\test_email_can_be_verified',
        2 => 'tests\\feature\\auth\\test_email_is_not_verified_with_invalid_hash',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/RegistrationTest.php' => 
    array (
      0 => 'eb584b567ce5ff34d1cc4bf89bb8e00dd481213d',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\registrationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\test_registration_screen_can_be_rendered',
        1 => 'tests\\feature\\auth\\test_new_users_can_register',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/AuthenticationTest.php' => 
    array (
      0 => 'cc810dbfdbc4e8458f3be441003682a1a8d01c85',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\authenticationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\test_login_screen_can_be_rendered',
        1 => 'tests\\feature\\auth\\test_users_can_authenticate_using_the_login_screen',
        2 => 'tests\\feature\\auth\\test_users_can_not_authenticate_with_invalid_password',
        3 => 'tests\\feature\\auth\\test_users_can_logout',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/PasswordResetTest.php' => 
    array (
      0 => '93b8ac73c926d535ae6b6c65c9370ba34816f13d',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\passwordresettest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\test_reset_password_link_screen_can_be_rendered',
        1 => 'tests\\feature\\auth\\test_reset_password_link_can_be_requested',
        2 => 'tests\\feature\\auth\\test_reset_password_screen_can_be_rendered',
        3 => 'tests\\feature\\auth\\test_password_can_be_reset_with_valid_token',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/NotionIntegrationTest.php' => 
    array (
      0 => '13d50d5144f9ab83158bdfc5e19bad99051226ff',
      1 => 
      array (
        0 => 'tests\\feature\\notionintegrationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\setup',
        1 => 'tests\\feature\\test_user_can_fetch_notion_pages_tree',
        2 => 'tests\\feature\\test_user_can_fetch_notion_pages_tree_with_page_id',
        3 => 'tests\\feature\\test_user_can_fetch_notion_databases',
        4 => 'tests\\feature\\test_user_can_fetch_notion_page',
        5 => 'tests\\feature\\test_user_can_fetch_notion_blocks',
        6 => 'tests\\feature\\test_user_without_notion_integration_cannot_access_endpoints',
        7 => 'tests\\feature\\test_user_with_inactive_notion_integration_cannot_access_endpoints',
        8 => 'tests\\feature\\test_unauthenticated_user_cannot_access_endpoints',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/DashboardTest.php' => 
    array (
      0 => '1c26384b1f0675e8a0e9ebaf02c9b593040c51cd',
      1 => 
      array (
        0 => 'tests\\feature\\dashboardtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_guests_are_redirected_to_the_login_page',
        1 => 'tests\\feature\\test_authenticated_users_can_visit_the_dashboard',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/IntegrationsTest.php' => 
    array (
      0 => '397be4197de111c69924d73504646f2f03613908',
      1 => 
      array (
        0 => 'tests\\feature\\integrationstest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_user_can_list_their_integrations',
        1 => 'tests\\feature\\test_user_can_create_an_integration',
        2 => 'tests\\feature\\test_user_cannot_create_duplicate_active_integration',
        3 => 'tests\\feature\\test_user_can_update_their_integration',
        4 => 'tests\\feature\\test_user_cannot_update_another_users_integration',
        5 => 'tests\\feature\\test_user_can_delete_their_integration',
        6 => 'tests\\feature\\test_user_cannot_delete_another_users_integration',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/NotionTest.php' => 
    array (
      0 => '0cd90fa8f469dee84c4074c0ff29c9df3780dd24',
      1 => 
      array (
        0 => 'tests\\feature\\notiontest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_guests_are_redirected_to_the_login_page',
        1 => 'tests\\feature\\test_authenticated_users_can_visit_the_notion_page',
        2 => 'tests\\feature\\test_api_returns_notion_pages_with_default_page_id',
        3 => 'tests\\feature\\test_api_returns_notion_pages_with_explicit_page_id',
        4 => 'tests\\feature\\test_api_handles_errors_with_default_page_id',
        5 => 'tests\\feature\\test_api_handles_errors_with_explicit_page_id',
        6 => 'tests\\feature\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/TestCase.php' => 
    array (
      0 => '5f6caea662bb5a5fc2e674f9cffcc9e5fff09a94',
      1 => 
      array (
        0 => 'tests\\testcase',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
  ),
));