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
      0 => 'dda1125e4c81d24303cc40b755d531d495c66cec',
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
      0 => '889d5143f651758ee8c5577e0585df7310507bd3',
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
      0 => '1435a9aa5b0c04faf8939f68981264d331eba95d',
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
      0 => 'a82d9ba12bc24c0e4295110d18bd232bb52966d4',
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
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Http/Controllers/AiChatControllerTest.php' => 
    array (
      0 => '0aee50af628461ca64e42ee7e3ca692af052109a',
      1 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\aichatcontrollertest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\setup',
        1 => 'tests\\feature\\http\\controllers\\test_chat_requires_authentication',
        2 => 'tests\\feature\\http\\controllers\\test_chat_validates_request_data',
        3 => 'tests\\feature\\http\\controllers\\test_chat_validates_message_structure',
        4 => 'tests\\feature\\http\\controllers\\test_chat_validates_model_parameter',
        5 => 'tests\\feature\\http\\controllers\\test_chat_successful_non_streaming_response',
        6 => 'tests\\feature\\http\\controllers\\test_chat_handles_mcp_server_error',
        7 => 'tests\\feature\\http\\controllers\\test_chat_handles_authentication_failure',
        8 => 'tests\\feature\\http\\controllers\\test_chat_extracts_last_message_content',
        9 => 'tests\\feature\\http\\controllers\\test_chat_returns_streamed_response_when_enabled',
        10 => 'tests\\feature\\http\\controllers\\test_chat_respects_temperature_parameter',
        11 => 'tests\\feature\\http\\controllers\\test_chat_respects_max_tokens_parameter',
        12 => 'tests\\feature\\http\\controllers\\test_chat_handles_network_exception',
        13 => 'tests\\feature\\http\\controllers\\test_chat_validates_empty_messages_array',
        14 => 'tests\\feature\\http\\controllers\\test_chat_validates_message_content_exists',
        15 => 'tests\\feature\\http\\controllers\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Http/Controllers/TodoistIntegrationControllerTest.php' => 
    array (
      0 => '39da4c452cab7b37fa37bd7d9fc84aad81fe3248',
      1 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\todoistintegrationcontrollertest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\setup',
        1 => 'tests\\feature\\http\\controllers\\test_show_displays_setup_page',
        2 => 'tests\\feature\\http\\controllers\\test_show_displays_existing_integration',
        3 => 'tests\\feature\\http\\controllers\\test_connect_requires_api_token',
        4 => 'tests\\feature\\http\\controllers\\test_disconnect_removes_active_integration',
        5 => 'tests\\feature\\http\\controllers\\test_test_connection_requires_active_integration',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Http/Controllers/DailyPlanningControllerTest.php' => 
    array (
      0 => 'f9c14f83d37273b442f33951319217ddc671bb14',
      1 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\dailyplanningcontrollertest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\http\\controllers\\setup',
        1 => 'tests\\feature\\http\\controllers\\test_index_requires_authentication',
        2 => 'tests\\feature\\http\\controllers\\test_index_requires_active_todoist_integration',
        3 => 'tests\\feature\\http\\controllers\\test_index_returns_daily_planning_page',
        4 => 'tests\\feature\\http\\controllers\\test_generate_creates_new_daily_plan',
        5 => 'tests\\feature\\http\\controllers\\test_generate_handles_todoist_api_error',
        6 => 'tests\\feature\\http\\controllers\\test_update_tasks_updates_todoist_tasks',
        7 => 'tests\\feature\\http\\controllers\\test_update_tasks_requires_existing_plan',
        8 => 'tests\\feature\\http\\controllers\\test_generate_applies_ivy_lee_method',
        9 => 'tests\\feature\\http\\controllers\\test_generate_prioritizes_p1_tasks_first',
        10 => 'tests\\feature\\http\\controllers\\test_generate_includes_breaks_in_time_blocks',
        11 => 'tests\\feature\\http\\controllers\\test_generate_detects_time_conflicts',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Actions/DailyPlanning/UpdateTodoistTasksActionTest.php' => 
    array (
      0 => '54cea2d996d0a43b075588f9957c0ec03768c59f',
      1 => 
      array (
        0 => 'tests\\unit\\actions\\dailyplanning\\updatetodoisttasksactiontest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\actions\\dailyplanning\\setup',
        1 => 'tests\\unit\\actions\\dailyplanning\\test_updates_all_tasks_successfully',
        2 => 'tests\\unit\\actions\\dailyplanning\\test_updates_partial_tasks_with_selected_types',
        3 => 'tests\\unit\\actions\\dailyplanning\\test_handles_none_update_type',
        4 => 'tests\\unit\\actions\\dailyplanning\\test_validates_update_parameters',
        5 => 'tests\\unit\\actions\\dailyplanning\\test_requires_existing_planning',
        6 => 'tests\\unit\\actions\\dailyplanning\\test_requires_active_todoist_integration',
        7 => 'tests\\unit\\actions\\dailyplanning\\test_handles_todoist_api_errors_gracefully',
        8 => 'tests\\unit\\actions\\dailyplanning\\test_handles_planning_not_found_error',
        9 => 'tests\\unit\\actions\\dailyplanning\\test_updates_task_schedule_correctly',
        10 => 'tests\\unit\\actions\\dailyplanning\\test_generates_success_message_with_counts',
        11 => 'tests\\unit\\actions\\dailyplanning\\createplanningdata',
        12 => 'tests\\unit\\actions\\dailyplanning\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Actions/DailyPlanning/CreateDailyPlanningActionTest.php' => 
    array (
      0 => 'ec54ee4c010f0337e63b29feac2441c958512a03',
      1 => 
      array (
        0 => 'tests\\unit\\actions\\dailyplanning\\createdailyplanningactiontest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\actions\\dailyplanning\\setup',
        1 => 'tests\\unit\\actions\\dailyplanning\\test_creates_daily_planning_successfully',
        2 => 'tests\\unit\\actions\\dailyplanning\\test_handles_no_tasks_found',
        3 => 'tests\\unit\\actions\\dailyplanning\\test_validates_input_parameters',
        4 => 'tests\\unit\\actions\\dailyplanning\\test_accepts_valid_preferences',
        5 => 'tests\\unit\\actions\\dailyplanning\\test_handles_service_exception',
        6 => 'tests\\unit\\actions\\dailyplanning\\test_generates_markdown_output',
        7 => 'tests\\unit\\actions\\dailyplanning\\test_stores_planning_with_unique_id',
        8 => 'tests\\unit\\actions\\dailyplanning\\test_planning_cache_expires_after_24_hours',
        9 => 'tests\\unit\\actions\\dailyplanning\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Services/DailyPlanningServiceTest.php' => 
    array (
      0 => 'dbcf51d703996a2e36a31a3e7f6b0139691ee466',
      1 => 
      array (
        0 => 'tests\\unit\\services\\dailyplanningservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\setup',
        1 => 'tests\\unit\\services\\test_generates_daily_planning_with_tasks',
        2 => 'tests\\unit\\services\\test_returns_no_tasks_message_when_empty',
        3 => 'tests\\unit\\services\\test_throws_exception_when_no_active_integration',
        4 => 'tests\\unit\\services\\test_applies_ivy_lee_method_limiting_to_6_tasks',
        5 => 'tests\\unit\\services\\test_prioritizes_p1_tasks_first',
        6 => 'tests\\unit\\services\\test_identifies_mit_correctly',
        7 => 'tests\\unit\\services\\test_creates_time_blocks_with_breaks',
        8 => 'tests\\unit\\services\\test_generates_alerts_for_p1_overload',
        9 => 'tests\\unit\\services\\test_generates_alerts_for_duration_overload',
        10 => 'tests\\unit\\services\\test_extracts_energy_levels_from_labels',
        11 => 'tests\\unit\\services\\test_respects_scheduled_times',
        12 => 'tests\\unit\\services\\test_generates_summary_statistics',
        13 => 'tests\\unit\\services\\test_prepares_todoist_updates',
        14 => 'tests\\unit\\services\\teardown',
      ),
      3 => 
      array (
      ),
    ),
  ),
));