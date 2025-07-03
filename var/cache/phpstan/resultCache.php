<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1751536560,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '2.1.17',
  'metaExtensions' => 
  array (
  ),
  'phpVersion' => 80401,
  'projectConfig' => '{parameters: {level: max, paths: [/Users/fred/PhpstormProjects/mcp_manager/app, /Users/fred/PhpstormProjects/mcp_manager/config, /Users/fred/PhpstormProjects/mcp_manager/database, /Users/fred/PhpstormProjects/mcp_manager/routes, /Users/fred/PhpstormProjects/mcp_manager/tests], excludePaths: {analyseAndScan: [/Users/fred/PhpstormProjects/mcp_manager/vendor, /Users/fred/PhpstormProjects/mcp_manager/node_modules, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/Auth, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/Settings, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/HandleInertiaRequests.php, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/CookieAttributes.php, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/DebugAuth.php, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/HasActiveNotionIntegration.php, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Requests/Auth/LoginRequest.php, /Users/fred/PhpstormProjects/mcp_manager/app/Http/Requests/Settings/ProfileUpdateRequest.php], analyse: []}, tmpDir: /Users/fred/PhpstormProjects/mcp_manager/var/cache/phpstan}}',
  'analysedPaths' => 
  array (
    0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
    1 => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
    2 => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
    3 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning',
    4 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
    5 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
  ),
  'scannedFiles' => 
  array (
    '/Users/fred/PhpstormProjects/mcp_manager/app/Console/Commands/RenameProjectCommand.php' => 'ae78ce2b1a0f3909f8669c320de903edf5401b09',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Enums/IntegrationStatus.php' => '421116c15a24e70db84d44592d004542c32f1be9',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Enums/IntegrationType.php' => 'a532f5779e9780eced189e6739b4dfdbaa5a7b18',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/AiChatController.php' => '89191e9fdc513866bbcb3c965962f1d9d6c5ddc5',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/CalendarController.php' => '04de98498f05d6cc8552f0fcf31ce4bd4d0608ca',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/Controller.php' => 'a33a5105f92c73a309c9f8a549905dcdf6dccbae',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/GmailController.php' => 'c5ec4970be7130eda85eb8d4dc68307938db164a',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/GoogleIntegrationController.php' => 'ef4bdcef2abd495053cb18970ba5843deb185c64',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/IntegrationsController.php' => 'dec7f34f5caf31df5904b41d70aafd511d1f08f8',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/McpProxyController.php' => '1b186f57430e85612a60a56d0f193b29ba757b71',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/NaturalLanguageController.php' => '9223352a2159007249e5125195269fd808d1305e',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/NotionController.php' => 'e5d76fda1a93e89bfe2398bca4c8f618a8fcabf3',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/NotionIntegrationController.php' => '097b5475565d2000bc98758d5dcf4fe32d518540',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/Authenticate.php' => 'aa7a909ab8e4a8d509f2cecfe890fd7f19df8938',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/HandleAppearance.php' => '919b506864fbdf2567a2e1c71b3583c418b06a86',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/HasActiveIntegration.php' => 'ad84ecb0bff5fb8f755cbc59b9744594390091fa',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Middleware/VerifyCsrfToken.php' => 'cd7982fbca8319ce828d330f06be6b2131101ff2',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Models/IntegrationAccount.php' => '7f0ab3becb323a5e0533e470f08fa783bacee612',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Models/User.php' => '254b4547d8d7443a538f26fb79cb39be7d8a4023',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Providers/AppServiceProvider.php' => '01bf9e5cf5bb666446625056b618445ae4749675',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Services/GoogleService.php' => 'df4a525b23fe4b69ab266394c8aef46999f278db',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Services/McpAuthService.php' => 'ebe40a55b8efca497ee3c8bf431239ff8a002851',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Services/NaturalLanguageService.php' => '7e782b26d82b2e6e6f99d758606060b1ac6d3dc5',
    '/Users/fred/PhpstormProjects/mcp_manager/app/Services/NotionService.php' => '753e6e750598e8e1da0cf7b434f2bcbd5bc731c0',
    '/Users/fred/PhpstormProjects/mcp_manager/config/app.php' => 'be30e3c7d232c4616f527cdab9a00333e072d0d5',
    '/Users/fred/PhpstormProjects/mcp_manager/config/auth.php' => 'a36d9e309e8385c9a6dab6cefa28475cc469a81c',
    '/Users/fred/PhpstormProjects/mcp_manager/config/cache.php' => '2b9663d1fa1e080d4c8acdfb58bb61740c6995da',
    '/Users/fred/PhpstormProjects/mcp_manager/config/cors.php' => '84905d5f310879ca3c061353deb70e7d41278694',
    '/Users/fred/PhpstormProjects/mcp_manager/config/database.php' => '0cd65306a4b60a3c5be526c2e828055586e3f4a7',
    '/Users/fred/PhpstormProjects/mcp_manager/config/filesystems.php' => '6e1e66753542ecbccfe730cfee0d623723be2986',
    '/Users/fred/PhpstormProjects/mcp_manager/config/inertia.php' => '8cab160c193370535e9d62c72d77c556881831f5',
    '/Users/fred/PhpstormProjects/mcp_manager/config/logging.php' => '183f235b749cafd7a98c96d71ab6f23853323fde',
    '/Users/fred/PhpstormProjects/mcp_manager/config/mail.php' => '9deb4512c1a2d5ad4593228c435012d0704ba0f7',
    '/Users/fred/PhpstormProjects/mcp_manager/config/queue.php' => '258c42a365b1b4bee36b69053966a3fd836a9394',
    '/Users/fred/PhpstormProjects/mcp_manager/config/services.php' => 'fdc45cc550bee36f7c97dafb4df8278d0ba60d10',
    '/Users/fred/PhpstormProjects/mcp_manager/config/session.php' => '5e45cc2560adfab39fa7dd2934c8eb386451ebb3',
    '/Users/fred/PhpstormProjects/mcp_manager/database/factories/IntegrationAccountFactory.php' => 'b9c5b99e48d080ff345ee2c72a97630827c05e0e',
    '/Users/fred/PhpstormProjects/mcp_manager/database/factories/UserFactory.php' => '7ac74334b97dded2308b4265ca46014b317a82f9',
    '/Users/fred/PhpstormProjects/mcp_manager/database/migrations/0001_01_01_000000_create_users_table.php' => 'c83722f2f43dc31195e37312e72524af995c15a9',
    '/Users/fred/PhpstormProjects/mcp_manager/database/migrations/0001_01_01_000001_create_cache_table.php' => '1e63143baede25661ec2075259ba517cbf2c2400',
    '/Users/fred/PhpstormProjects/mcp_manager/database/migrations/0001_01_01_000002_create_jobs_table.php' => '61d635023428eaa5cc6f27e5b7f9683817125a50',
    '/Users/fred/PhpstormProjects/mcp_manager/database/migrations/2023_06_10_000000_add_api_token_to_users_table.php' => '6fe7898830fcf923a1585fac57e744361f579e7c',
    '/Users/fred/PhpstormProjects/mcp_manager/database/migrations/2025_06_08_105450_create_integration_accounts_table.php' => '7ccae720da6f2b86e46ca6ffe7f6881692b95135',
    '/Users/fred/PhpstormProjects/mcp_manager/database/seeders/DatabaseSeeder.php' => '236a134dfcbb745a5a54b19f9647c3734106fd87',
    '/Users/fred/PhpstormProjects/mcp_manager/routes/api.php' => 'a68fc7b3b39c1cf1a87b5f9e270525be76763e5c',
    '/Users/fred/PhpstormProjects/mcp_manager/routes/auth.php' => '300ce76172c5954e85bf59b80c08b6b34f85369b',
    '/Users/fred/PhpstormProjects/mcp_manager/routes/console.php' => '302bdfc3b87dd1b70c1dc59645e1235395c9c0e3',
    '/Users/fred/PhpstormProjects/mcp_manager/routes/settings.php' => '2698384be23da6af0e0824b630f147ee047b366f',
    '/Users/fred/PhpstormProjects/mcp_manager/routes/web.php' => '18a9fec4415417c4cc5674817e5ba7a4ac749a78',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/AuthenticationTest.php' => 'cc810dbfdbc4e8458f3be441003682a1a8d01c85',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/EmailVerificationTest.php' => '52ab0195c3ce6089cbea9751454943220af6f767',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/PasswordConfirmationTest.php' => '3d1f8a8685b1aa7df2607b16f3f7d57ec4747244',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/PasswordResetTest.php' => '93b8ac73c926d535ae6b6c65c9370ba34816f13d',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Auth/RegistrationTest.php' => 'eb584b567ce5ff34d1cc4bf89bb8e00dd481213d',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/DashboardTest.php' => '1c26384b1f0675e8a0e9ebaf02c9b593040c51cd',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/IntegrationsTest.php' => '397be4197de111c69924d73504646f2f03613908',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/NotionIntegrationTest.php' => '1435a9aa5b0c04faf8939f68981264d331eba95d',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/NotionTest.php' => 'a82d9ba12bc24c0e4295110d18bd232bb52966d4',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Settings/PasswordUpdateTest.php' => '3674e7ce8ae5bde7d8498905b66631378f2bb145',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/Settings/ProfileUpdateTest.php' => '1b4ca326ef9fa66f76b124881a76a6589e84b11a',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/TestCase.php' => '5f6caea662bb5a5fc2e674f9cffcc9e5fff09a94',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/ExampleTest.php' => 'fc8106358d932d0e2dc66f70f07a1513b7420ae1',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Models/IntegrationAccountTest.php' => '4d3c94971c0dfa195c9f51c941c73e356d54d861',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/Services/NotionServiceTest.php' => '889d5143f651758ee8c5577e0585df7310507bd3',
    '/Users/fred/PhpstormProjects/mcp_manager/tests/Unit/StringUtilsTest.php' => 'dda1125e4c81d24303cc40b755d531d495c66cec',
  ),
  'composerLocks' => 
  array (
    '/Users/fred/PhpstormProjects/mcp_manager/composer.lock' => '5d79bf59b706a57eb664875aca6f62371e2d2c38',
  ),
  'composerInstalled' => 
  array (
    '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'brick/math' => 
        array (
          'pretty_version' => '0.12.3',
          'version' => '0.12.3.0',
          'reference' => '866551da34e9a618e64a819ee1e01c20d8a588ba',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../brick/math',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'carbonphp/carbon-doctrine-types' => 
        array (
          'pretty_version' => '3.2.0',
          'version' => '3.2.0.0',
          'reference' => '18ba5ddfec8976260ead6e866180bd5d2f71aa1d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../carbonphp/carbon-doctrine-types',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'cordoval/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'davedevelopment/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'dflydev/dot-access-data' => 
        array (
          'pretty_version' => 'v3.0.3',
          'version' => '3.0.3.0',
          'reference' => 'a23a2bf4f31d3518f3ecb38660c95715dfead60f',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../dflydev/dot-access-data',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/inflector' => 
        array (
          'pretty_version' => '2.0.10',
          'version' => '2.0.10.0',
          'reference' => '5817d0659c5b50c9b950feb9af7b9668e2c436bc',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../doctrine/inflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/lexer' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => '31ad66abc0fc9e1a1f2d9bc6a42668d2fbbcd6dd',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../doctrine/lexer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'dragonmantank/cron-expression' => 
        array (
          'pretty_version' => 'v3.4.0',
          'version' => '3.4.0.0',
          'reference' => '8c784d071debd117328803d86b2097615b457500',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../dragonmantank/cron-expression',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'egulias/email-validator' => 
        array (
          'pretty_version' => '4.0.4',
          'version' => '4.0.4.0',
          'reference' => 'd42c8731f0624ad6bdc8d3e5e9a4524f68801cfa',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../egulias/email-validator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'fakerphp/faker' => 
        array (
          'pretty_version' => 'v1.24.1',
          'version' => '1.24.1.0',
          'reference' => 'e0ee18eb1e6dc3cda3ce9fd97e5a0689a88a64b5',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../fakerphp/faker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'filp/whoops' => 
        array (
          'pretty_version' => '2.18.0',
          'version' => '2.18.0.0',
          'reference' => 'a7de6c3c6c3c022f5cfc337f8ede6a14460cf77e',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../filp/whoops',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fruitcake/php-cors' => 
        array (
          'pretty_version' => 'v1.3.0',
          'version' => '1.3.0.0',
          'reference' => '3d158f36e7875e2f040f37bc0573956240a5a38b',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../fruitcake/php-cors',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'graham-campbell/result-type' => 
        array (
          'pretty_version' => 'v1.1.3',
          'version' => '1.1.3.0',
          'reference' => '3ba905c11371512af9d9bdd27d99b782216b6945',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../graham-campbell/result-type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/guzzle' => 
        array (
          'pretty_version' => '7.9.3',
          'version' => '7.9.3.0',
          'reference' => '7b2f29fe81dc4da0ca0ea7d42107a0845946ea77',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../guzzlehttp/guzzle',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/promises' => 
        array (
          'pretty_version' => '2.2.0',
          'version' => '2.2.0.0',
          'reference' => '7c69f28996b0a6920945dd20b3857e499d9ca96c',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../guzzlehttp/promises',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/psr7' => 
        array (
          'pretty_version' => '2.7.1',
          'version' => '2.7.1.0',
          'reference' => 'c2270caaabe631b3b44c85f99e5a04bbb8060d16',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../guzzlehttp/psr7',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/uri-template' => 
        array (
          'pretty_version' => 'v1.0.4',
          'version' => '1.0.4.0',
          'reference' => '30e286560c137526eccd4ce21b2de477ab0676d2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../guzzlehttp/uri-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'hamcrest/hamcrest-php' => 
        array (
          'pretty_version' => 'v2.1.1',
          'version' => '2.1.1.0',
          'reference' => 'f8b1c0173b22fa6ec77a81fe63e5b01eba7e6487',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../hamcrest/hamcrest-php',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'illuminate/auth' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/broadcasting' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/bus' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/cache' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/collections' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/concurrency' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/conditionable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/config' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/console' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/container' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/contracts' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/cookie' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/database' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/encryption' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/events' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/filesystem' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/hashing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/http' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/log' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/macroable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/mail' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/notifications' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/pagination' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/pipeline' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/process' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/queue' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/redis' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/routing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/session' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/support' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/testing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/translation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/validation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'illuminate/view' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.16.0',
          ),
        ),
        'inertiajs/inertia-laravel' => 
        array (
          'pretty_version' => 'v2.0.2',
          'version' => '2.0.2.0',
          'reference' => '248e815cf8d41307cbfb735efaa514c118e2f3b4',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../inertiajs/inertia-laravel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'kodova/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'laravel/breeze' => 
        array (
          'pretty_version' => 'v2.3.6',
          'version' => '2.3.6.0',
          'reference' => '390cbc433cb72fa6050965000b2d56c9ba6fd713',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/breeze',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/framework' => 
        array (
          'pretty_version' => 'v12.16.0',
          'version' => '12.16.0.0',
          'reference' => '293bb1c70224faebfd3d4328e201c37115da055f',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/framework',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/pail' => 
        array (
          'pretty_version' => 'v1.2.2',
          'version' => '1.2.2.0',
          'reference' => 'f31f4980f52be17c4667f3eafe034e6826787db2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/pail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/pint' => 
        array (
          'pretty_version' => 'v1.22.1',
          'version' => '1.22.1.0',
          'reference' => '941d1927c5ca420c22710e98420287169c7bcaf7',
          'type' => 'project',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/pint',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/prompts' => 
        array (
          'pretty_version' => 'v0.3.5',
          'version' => '0.3.5.0',
          'reference' => '57b8f7efe40333cdb925700891c7d7465325d3b1',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/prompts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/sail' => 
        array (
          'pretty_version' => 'v1.43.1',
          'version' => '1.43.1.0',
          'reference' => '3e7d899232a8c5e3ea4fc6dee7525ad583887e72',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/sail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/serializable-closure' => 
        array (
          'pretty_version' => 'v2.0.4',
          'version' => '2.0.4.0',
          'reference' => 'b352cf0534aa1ae6b4d825d1e762e35d43f8a841',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/serializable-closure',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/tinker' => 
        array (
          'pretty_version' => 'v2.10.1',
          'version' => '2.10.1.0',
          'reference' => '22177cc71807d38f2810c6204d8f7183d88a57d3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../laravel/tinker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/commonmark' => 
        array (
          'pretty_version' => '2.7.0',
          'version' => '2.7.0.0',
          'reference' => '6fbb36d44824ed4091adbcf4c7d4a3923cdb3405',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/commonmark',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/config' => 
        array (
          'pretty_version' => 'v1.2.0',
          'version' => '1.2.0.0',
          'reference' => '754b3604fb2984c71f4af4a9cbe7b57f346ec1f3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/config',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem' => 
        array (
          'pretty_version' => '3.29.1',
          'version' => '3.29.1.0',
          'reference' => 'edc1bb7c86fab0776c3287dbd19b5fa278347319',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/flysystem',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem-local' => 
        array (
          'pretty_version' => '3.29.0',
          'version' => '3.29.0.0',
          'reference' => 'e0e8d52ce4b2ed154148453d321e97c8e931bd27',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/flysystem-local',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/mime-type-detection' => 
        array (
          'pretty_version' => '1.16.0',
          'version' => '1.16.0.0',
          'reference' => '2d6702ff215bf922936ccc1ad31007edc76451b9',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/mime-type-detection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri' => 
        array (
          'pretty_version' => '7.5.1',
          'version' => '7.5.1.0',
          'reference' => '81fb5145d2644324614cc532b28efd0215bda430',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/uri',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri-interfaces' => 
        array (
          'pretty_version' => '7.5.0',
          'version' => '7.5.0.0',
          'reference' => '08cfc6c4f3d811584fb09c37e2849e6a7f9b0742',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../league/uri-interfaces',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'mockery/mockery' => 
        array (
          'pretty_version' => '1.6.12',
          'version' => '1.6.12.0',
          'reference' => '1f4efdd7d3beafe9807b08156dfcb176d18f1699',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../mockery/mockery',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'monolog/monolog' => 
        array (
          'pretty_version' => '3.9.0',
          'version' => '3.9.0.0',
          'reference' => '10d85740180ecba7896c87e06a166e0c95a0e3b6',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../monolog/monolog',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'mtdowling/cron-expression' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '^1.0',
          ),
        ),
        'myclabs/deep-copy' => 
        array (
          'pretty_version' => '1.13.1',
          'version' => '1.13.1.0',
          'reference' => '1720ddd719e16cf0db4eb1c6eca108031636d46c',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../myclabs/deep-copy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nesbot/carbon' => 
        array (
          'pretty_version' => '3.9.1',
          'version' => '3.9.1.0',
          'reference' => 'ced71f79398ece168e24f7f7710462f462310d4d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nesbot/carbon',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/schema' => 
        array (
          'pretty_version' => 'v1.3.2',
          'version' => '1.3.2.0',
          'reference' => 'da801d52f0354f70a638673c4a0f04e16529431d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nette/schema',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/utils' => 
        array (
          'pretty_version' => 'v4.0.6',
          'version' => '4.0.6.0',
          'reference' => 'ce708655043c7050eb050df361c5e313cf708309',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nette/utils',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nikic/php-parser' => 
        array (
          'pretty_version' => 'v5.4.0',
          'version' => '5.4.0.0',
          'reference' => '447a020a1f875a434d62f2a401f53b82a396e494',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nikic/php-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nunomaduro/collision' => 
        array (
          'pretty_version' => 'v8.8.0',
          'version' => '8.8.0.0',
          'reference' => '4cf9f3b47afff38b139fb79ce54fc71799022ce8',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nunomaduro/collision',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nunomaduro/termwind' => 
        array (
          'pretty_version' => 'v2.3.1',
          'version' => '2.3.1.0',
          'reference' => 'dfa08f390e509967a15c22493dc0bac5733d9123',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../nunomaduro/termwind',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'phar-io/manifest' => 
        array (
          'pretty_version' => '2.0.4',
          'version' => '2.0.4.0',
          'reference' => '54750ef60c58e43759730615a392c31c80e23176',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phar-io/manifest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/version' => 
        array (
          'pretty_version' => '3.2.1',
          'version' => '3.2.1.0',
          'reference' => '4f7fd7836c6f332bb2933569e566a0d6c4cbed74',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phar-io/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'php-di/invoker' => 
        array (
          'pretty_version' => '2.3.6',
          'version' => '2.3.6.0',
          'reference' => '59f15608528d8a8838d69b422a919fd6b16aa576',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../php-di/invoker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'php-di/php-di' => 
        array (
          'pretty_version' => '7.0.11',
          'version' => '7.0.11.0',
          'reference' => '32f111a6d214564520a57831d397263e8946c1d2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../php-di/php-di',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'phpoption/phpoption' => 
        array (
          'pretty_version' => '1.9.3',
          'version' => '1.9.3.0',
          'reference' => 'e3fac8b24f56113f7cb96af14958c0dd16330f54',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpoption/phpoption',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '2.1.17',
          'version' => '2.1.17.0',
          'reference' => '89b5ef665716fa2a52ecd2633f21007a6a349053',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-code-coverage' => 
        array (
          'pretty_version' => '11.0.9',
          'version' => '11.0.9.0',
          'reference' => '14d63fbcca18457e49c6f8bebaa91a87e8e188d7',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/php-code-coverage',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-file-iterator' => 
        array (
          'pretty_version' => '5.1.0',
          'version' => '5.1.0.0',
          'reference' => '118cfaaa8bc5aef3287bf315b6060b1174754af6',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/php-file-iterator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-invoker' => 
        array (
          'pretty_version' => '5.0.1',
          'version' => '5.0.1.0',
          'reference' => 'c1ca3814734c07492b3d4c5f794f4b0995333da2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/php-invoker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-text-template' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '3e0404dc6b300e6bf56415467ebcb3fe4f33e964',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/php-text-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-timer' => 
        array (
          'pretty_version' => '7.0.1',
          'version' => '7.0.1.0',
          'reference' => '3b415def83fbcb41f991d9ebf16ae4ad8b7837b3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/php-timer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/phpunit' => 
        array (
          'pretty_version' => '11.5.21',
          'version' => '11.5.21.0',
          'reference' => 'd565e2cdc21a7db9dc6c399c1fc2083b8010f289',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../phpunit/phpunit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/clock' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'e41a24703d4560fd0acb709162f73b8adfc3aa0d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/clock-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/container' => 
        array (
          'pretty_version' => '2.0.2',
          'version' => '2.0.2.0',
          'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/container',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/container-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.1|2.0',
            1 => '^1.0',
          ),
        ),
        'psr/event-dispatcher' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-client' => 
        array (
          'pretty_version' => '1.0.3',
          'version' => '1.0.3.0',
          'reference' => 'bb5906edc1c324c9a05aa0873d40117941e5fa90',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/http-client',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-client-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-factory' => 
        array (
          'pretty_version' => '1.1.0',
          'version' => '1.1.0.0',
          'reference' => '2b4765fddfe3b508ac62f829e852b1501d3f6e8a',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/http-factory',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-factory-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-message' => 
        array (
          'pretty_version' => '2.0',
          'version' => '2.0.0.0',
          'reference' => '402d35bcb92c70c026d1a6a9883f06b2ead23d71',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/http-message',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-message-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/log' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/log',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/log-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
            1 => '3.0.0',
          ),
        ),
        'psr/simple-cache' => 
        array (
          'pretty_version' => '3.0.0',
          'version' => '3.0.0.0',
          'reference' => '764e0b3939f5ca87cb904f570ef9be2d78a07865',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psr/simple-cache',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/simple-cache-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
          ),
        ),
        'psy/psysh' => 
        array (
          'pretty_version' => 'v0.12.8',
          'version' => '0.12.8.0',
          'reference' => '85057ceedee50c49d4f6ecaff73ee96adb3b3625',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../psy/psysh',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ralouphie/getallheaders' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => '120b605dfeb996808c31b6477290a714d356e822',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../ralouphie/getallheaders',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/collection' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '344572933ad0181accbf4ba763e85a0306a8c5e2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../ramsey/collection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/uuid' => 
        array (
          'pretty_version' => '4.7.6',
          'version' => '4.7.6.0',
          'reference' => '91039bc1faa45ba123c4328958e620d382ec7088',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../ramsey/uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'rector/rector' => 
        array (
          'pretty_version' => '2.0.16',
          'version' => '2.0.16.0',
          'reference' => 'f1366d1f8c7490541c8f7af6e5c7cef7cca1b5a2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../rector/rector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'rhumsaa/uuid' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '4.7.6',
          ),
        ),
        'sebastian/cli-parser' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => '15c5dd40dc4f38794d383bb95465193f5e0ae180',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/cli-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => '54391c61e4af8078e5b276ab082b6d3c54c9ad64',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/code-unit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit-reverse-lookup' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '183a9b2632194febd219bb9246eee421dad8d45e',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/code-unit-reverse-lookup',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/comparator' => 
        array (
          'pretty_version' => '6.3.1',
          'version' => '6.3.1.0',
          'reference' => '24b8fbc2c8e201bb1308e7b05148d6ab393b6959',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/comparator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/complexity' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => 'ee41d384ab1906c68852636b6de493846e13e5a0',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/complexity',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/diff' => 
        array (
          'pretty_version' => '6.0.2',
          'version' => '6.0.2.0',
          'reference' => 'b4ccd857127db5d41a5b676f24b51371d76d8544',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/diff',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/environment' => 
        array (
          'pretty_version' => '7.2.1',
          'version' => '7.2.1.0',
          'reference' => 'a5c75038693ad2e8d4b6c15ba2403532647830c4',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/environment',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/exporter' => 
        array (
          'pretty_version' => '6.3.0',
          'version' => '6.3.0.0',
          'reference' => '3473f61172093b2da7de1fb5782e1f24cc036dc3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/exporter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/global-state' => 
        array (
          'pretty_version' => '7.0.2',
          'version' => '7.0.2.0',
          'reference' => '3be331570a721f9a4b5917f4209773de17f747d7',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/global-state',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/lines-of-code' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => 'd36ad0d782e5756913e42ad87cb2890f4ffe467a',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/lines-of-code',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-enumerator' => 
        array (
          'pretty_version' => '6.0.1',
          'version' => '6.0.1.0',
          'reference' => 'f5b498e631a74204185071eb41f33f38d64608aa',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/object-enumerator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-reflector' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '6e1a43b411b2ad34146dee7524cb13a068bb35f9',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/object-reflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/recursion-context' => 
        array (
          'pretty_version' => '6.0.2',
          'version' => '6.0.2.0',
          'reference' => '694d156164372abbd149a4b85ccda2e4670c0e16',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/recursion-context',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/type' => 
        array (
          'pretty_version' => '5.1.2',
          'version' => '5.1.2.0',
          'reference' => 'a8a7e30534b0eb0c77cd9d07e82de1a114389f5e',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/version' => 
        array (
          'pretty_version' => '5.0.2',
          'version' => '5.0.2.0',
          'reference' => 'c687e3387b99f5b03b6caa64c74b63e2936ff874',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../sebastian/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'spatie/backtrace' => 
        array (
          'pretty_version' => '1.7.4',
          'version' => '1.7.4.0',
          'reference' => 'cd37a49fce7137359ac30ecc44ef3e16404cccbe',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../spatie/backtrace',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/laravel-ray' => 
        array (
          'pretty_version' => '1.40.2',
          'version' => '1.40.2.0',
          'reference' => '1d1b31eb83cb38b41975c37363c7461de6d86b25',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../spatie/laravel-ray',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/macroable' => 
        array (
          'pretty_version' => '2.0.0',
          'version' => '2.0.0.0',
          'reference' => 'ec2c320f932e730607aff8052c44183cf3ecb072',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../spatie/macroable',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/once' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'spatie/ray' => 
        array (
          'pretty_version' => '1.42.0',
          'version' => '1.42.0.0',
          'reference' => '152250ce7c490bf830349fa30ba5200084e95860',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../spatie/ray',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'staabm/side-effects-detector' => 
        array (
          'pretty_version' => '1.0.5',
          'version' => '1.0.5.0',
          'reference' => 'd8334211a140ce329c13726d4a715adbddd0a163',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../staabm/side-effects-detector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/clock' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'b81435fbd6648ea425d1ee96a2d8e68f4ceacd24',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/console' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '66c1440edf6f339fd82ed6c7caa76cb006211b44',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/console',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/css-selector' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '601a5ce9aaad7bf10797e3663faefce9e26c24e2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/css-selector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/deprecation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '63afe740e99a13ba87ec199bb07bbdee937a5b62',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/deprecation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/error-handler' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'cf68d225bc43629de4ff54778029aee6dc191b83',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/error-handler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '497f73ac996a598c92409b44ac43b6690c4f666d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '59eb412e93815df44f05f342958efa9f46b1e586',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/event-dispatcher-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.0|3.0',
          ),
        ),
        'symfony/finder' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'ec2344cf77a48253bbca6939aa3d2477773ea63d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/finder',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-foundation' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '4236baf01609667d53b20371486228231eb135fd',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/http-foundation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-kernel' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'ac7b8e163e8c83dce3abcc055a502d4486051a9f',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/http-kernel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mailer' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '0f375bbbde96ae8c78e4aa3e63aabd486e33364c',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/mailer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mime' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '0e7b19b2f399c31df0cdbe5d8cbf53f02f6cfcd9',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/mime',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-ctype' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => 'a3cc8b044a6ea513310cbd48ef7333b384945638',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-ctype',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-iconv' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '5f3b930437ae03ae5dff61269024d8ea1b3774aa',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-iconv',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-grapheme' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => 'b9123926e3b7bc2f98c02ad54f6a4b02b91a8abe',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-intl-grapheme',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-idn' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '9614ac4d8061dc257ecc64cba1b140873dce8ad3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-intl-idn',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-normalizer' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '3833d7255cc303546435cb650316bff708a1c75c',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-intl-normalizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '6d857f4d76bd4b343eac26d6b539585d2bc56493',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-mbstring',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php80' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '0cc9dd0f17f61d8131e7df6b84bd344899fe2608',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-php80',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php83' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '2fb86d65e2d424369ad2905e83b236a8805ba491',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-php83',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-uuid' => 
        array (
          'pretty_version' => 'v1.32.0',
          'version' => '1.32.0.0',
          'reference' => '21533be36c24be3f4b1669c4725c7d1d2bab4ae2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/polyfill-uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/process' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '40c295f2deb408d5e9d2d32b8ba1dd61e36f05af',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/process',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/routing' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '8e213820c5fea844ecea29203d2a308019007c15',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/routing',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/service-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'f021b05a130d35510bd6b25fe9053c2a8a15d5d4',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/service-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/stopwatch' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '5a49289e2b308214c8b9c2fda4ea454d8b8ad7cd',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/stopwatch',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/string' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'f3570b8c61ca887a9e2938e85cb6458515d2b125',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/string',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '4aba29076a29a3aa667e09b791e5f868973a8667',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/translation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'df210c7a2573f1913b2d17cc95f90f53a73d8f7d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/translation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.3|3.0',
          ),
        ),
        'symfony/uid' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '7beeb2b885cd584cd01e126c5777206ae4c3c6a3',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/uid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/var-dumper' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '548f6760c54197b1084e1e5c71f6d9d523f2f78e',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/var-dumper',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/yaml' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'cea40a48279d58dc3efee8112634cb90141156c2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../symfony/yaml',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'theseer/tokenizer' => 
        array (
          'pretty_version' => '1.2.3',
          'version' => '1.2.3.0',
          'reference' => '737eda637ed5e28c3413cb1ebe8bb52cbf1ca7a2',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../theseer/tokenizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'tightenco/ziggy' => 
        array (
          'pretty_version' => 'v2.5.3',
          'version' => '2.5.3.0',
          'reference' => '0b3b521d2c55fbdb04b6721532f7f5f49d32f52b',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../tightenco/ziggy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'tijsverkoyen/css-to-inline-styles' => 
        array (
          'pretty_version' => 'v2.3.0',
          'version' => '2.3.0.0',
          'reference' => '0d72ac1c00084279c1816675284073c5a337c20d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../tijsverkoyen/css-to-inline-styles',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'vlucas/phpdotenv' => 
        array (
          'pretty_version' => 'v5.6.2',
          'version' => '5.6.2.0',
          'reference' => '24ac4c74f91ee2c193fa1aaa5c249cb0822809af',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../vlucas/phpdotenv',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'voku/portable-ascii' => 
        array (
          'pretty_version' => '2.0.3',
          'version' => '2.0.3.0',
          'reference' => 'b1d923f88091c6bf09699efcd7c8a1b1bfd7351d',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../voku/portable-ascii',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'webmozart/assert' => 
        array (
          'pretty_version' => '1.11.0',
          'version' => '1.11.0.0',
          'reference' => '11cb2199493b2f8a3b53e7f19068fc6aac760991',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../webmozart/assert',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'zbateson/mail-mime-parser' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => 'e0d4423fe27850c9dd301190767dbc421acc2f19',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../zbateson/mail-mime-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'zbateson/mb-wrapper' => 
        array (
          'pretty_version' => '2.0.1',
          'version' => '2.0.1.0',
          'reference' => '50a14c0c9537f978a61cde9fdc192a0267cc9cff',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../zbateson/mb-wrapper',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'zbateson/stream-decorators' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '32a2a62fb0f26313395c996ebd658d33c3f9c4e5',
          'type' => 'library',
          'install_path' => '/Users/fred/PhpstormProjects/mcp_manager/vendor/composer/../zbateson/stream-decorators',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    'phar:///Users/fred/PhpstormProjects/mcp_manager/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute.php' => 'eaf9127f074e9c7ebc65043ec4050f9fed60c2bb',
    'phar:///Users/fred/PhpstormProjects/mcp_manager/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar:///Users/fred/PhpstormProjects/mcp_manager/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar:///Users/fred/PhpstormProjects/mcp_manager/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'FFI',
    2 => 'PDO',
    3 => 'Phar',
    4 => 'Reflection',
    5 => 'SPL',
    6 => 'SimpleXML',
    7 => 'Zend OPcache',
    8 => 'bcmath',
    9 => 'bz2',
    10 => 'calendar',
    11 => 'ctype',
    12 => 'curl',
    13 => 'date',
    14 => 'dba',
    15 => 'dom',
    16 => 'exif',
    17 => 'fileinfo',
    18 => 'filter',
    19 => 'ftp',
    20 => 'gd',
    21 => 'gettext',
    22 => 'gmp',
    23 => 'hash',
    24 => 'iconv',
    25 => 'igbinary',
    26 => 'imagick',
    27 => 'imap',
    28 => 'intl',
    29 => 'json',
    30 => 'ldap',
    31 => 'libxml',
    32 => 'mbstring',
    33 => 'mongodb',
    34 => 'mysqli',
    35 => 'mysqlnd',
    36 => 'openssl',
    37 => 'pcntl',
    38 => 'pcre',
    39 => 'pdo_mysql',
    40 => 'pdo_pgsql',
    41 => 'pdo_sqlite',
    42 => 'pgsql',
    43 => 'posix',
    44 => 'random',
    45 => 'readline',
    46 => 'redis',
    47 => 'session',
    48 => 'shmop',
    49 => 'soap',
    50 => 'sockets',
    51 => 'sodium',
    52 => 'sqlite3',
    53 => 'standard',
    54 => 'sysvmsg',
    55 => 'sysvsem',
    56 => 'sysvshm',
    57 => 'tokenizer',
    58 => 'xml',
    59 => 'xmlreader',
    60 => 'xmlwriter',
    61 => 'xsl',
    62 => 'zip',
    63 => 'zlib',
    64 => 'zstd',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => 'max',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::__construct() has parameter $errors with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 9,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 9,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Deprecated in PHP 8.4: Parameter #2 $message (string) is implicitly nullable via default value null.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 17,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'parameter.implicitlyNullable',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::success() has parameter $data with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 17,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 17,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::error() has parameter $errors with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 27,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::validationError() has parameter $errors with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 37,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::toArray() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 56,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\ActionResult::toResponse() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 66,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::handle() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::validate() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 59,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::validate() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 59,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::authorize() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 64,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::execute() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 69,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::execute() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 69,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::execute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 69,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::beforeExecute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 74,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 74,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::afterExecute() has parameter $result with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::afterExecute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\BaseAction::logSuccess() has parameter $result with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 95,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 95,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::id().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 98,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 98,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::id().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'line' => 109,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 109,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::validate() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::validate() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Validation\\Factory|Illuminate\\Contracts\\Validation\\Validator::validate().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 24,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 24,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $data of function validator expects array|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 24,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 24,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::authorize() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method exists() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method integrationAccounts() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method where() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method where() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::authorize() should return bool but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::execute() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 47,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::execute() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 47,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::execute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 47,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Services\\DailyPlanningService::generateDailyPlanning() expects App\\Models\\User, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::storePlanning() expects App\\Models\\User, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 64,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access property $id on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 74,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 74,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.nonObject',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::afterExecute() has parameter $result with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 83,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 83,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::afterExecute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 83,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 83,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'success\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 85,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 85,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method causedBy() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 87,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method log() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 87,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method withProperties() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 87,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function activity not found.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 87,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::user().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 88,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'planning_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 90,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'planning\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'top_tasks\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of function count expects array|Countable, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'mit\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 92,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 92,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'planning\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 92,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 92,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Exceptions\\IntegrationException not found.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 100,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 100,
       'nodeType' => 'PhpParser\\Node\\Expr\\Instanceof_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::storePlanning() has parameter $planning with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 111,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 111,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\CreateDailyPlanningAction::generateMarkdown() has parameter $planning with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 124,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $mit[\'content\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $mit[\'priority\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $mit[\'project_name\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 139,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 139,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Binary operation "+" between mixed and 1 results in an error.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Plus',
       'identifier' => 'binaryOp.invalid',
       'metadata' => 
      array (
      ),
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 141,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 141,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $duration (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $task[\'content\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $task[\'priority\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $task[\'project_name\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'period\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 149,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $array of function array_filter expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 149,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'period\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 150,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 150,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $array of function array_filter expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 150,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 150,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    53 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'end\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    54 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'start\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    55 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'title\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    56 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'end\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    57 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'start\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    58 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'title\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 155,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 155,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    59 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'end\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    60 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'start\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    61 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'title\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    62 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'end\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    63 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'start\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    64 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $block[\'title\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    65 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    66 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    67 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    68 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $task[\'content\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    69 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $task[\'priority\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    70 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 194,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 194,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    71 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'severity\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 195,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 195,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    72 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'message\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 196,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    73 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'type\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 196,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    74 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $alert[\'message\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 196,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    75 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $alert[\'type\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 196,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    76 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'details\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 197,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 197,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    77 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 198,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 198,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    78 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task1\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    79 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task2\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    80 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    81 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $detail[\'task1\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    82 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $detail[\'task2\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    83 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $detail[\'time\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::validate() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 22,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 22,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::validate() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 22,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 22,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::authorize() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 38,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 38,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method exists() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method integrationAccounts() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method where() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method where() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access property $id on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.nonObject',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $planningId (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $user->id (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::execute() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::execute() has parameter $parameters with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::execute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access property $id on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.nonObject',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $planningId (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $user->id (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'type\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'selected\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 77,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $type of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::determineUpdates() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 77,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $selected of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::determineUpdates() expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 77,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::applyUpdates() expects App\\Models\\User, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 80,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $planning of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::applyUpdates() expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 80,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::afterExecute() has parameter $result with no type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 90,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::afterExecute() has parameter $validated with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 90,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'success\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 92,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 92,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method causedBy() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method log() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method withProperties() on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function activity not found.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::user().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 95,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 95,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'type\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 97,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 97,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'updates_applied\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 98,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 98,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of function count expects array|Countable, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 98,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 98,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Exceptions\\IntegrationException not found.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 114,
       'nodeType' => 'PhpParser\\Node\\Expr\\Instanceof_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::determineUpdates() has parameter $selected with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 125,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::determineUpdates() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 125,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::applyUpdates() has parameter $planning with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 134,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 134,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::applyUpdates() has parameter $updatesToApply with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 134,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 134,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::applyUpdates() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 134,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 134,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'schedule_updates\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 150,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 150,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 151,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 151,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $taskId of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskSchedule() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $time of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskSchedule() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 156,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 156,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $update[\'task_name\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 156,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 156,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration_updates\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 162,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 162,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 163,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 165,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 165,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $taskId of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskDuration() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 165,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $duration of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskDuration() expects int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 165,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    53 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 168,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 168,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    54 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $update[\'task_name\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 168,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 168,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    55 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'order_updates\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 174,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 174,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    56 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Argument of an invalid type mixed supplied for foreach, only iterables are supported.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 176,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 176,
       'nodeType' => 'PHPStan\\Node\\InForeachNode',
       'identifier' => 'foreach.nonIterable',
       'metadata' => 
      array (
      ),
    )),
    57 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'order\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    58 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    59 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $taskId of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskOrder() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    60 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $order of method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::updateTaskOrder() expects int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    61 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 181,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 181,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    62 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $update[\'task_name\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 181,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 181,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    63 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #3 $subject of function preg_replace expects array<float|int|string>|string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 212,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 212,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    64 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Binary operation "." between list<string>|string|null and non-falsy-string results in an error.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 216,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Concat',
       'identifier' => 'binaryOp.invalid',
       'metadata' => 
      array (
      ),
    )),
    65 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $string of function trim expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 216,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    66 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #3 $subject of function preg_replace expects array<float|int|string>|string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 230,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 230,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    67 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction::generateSuccessMessage() has parameter $results with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 240,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 240,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    68 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $results[\'schedule\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 245,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 245,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    69 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $results[\'duration\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 248,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    70 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $results[\'order\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 251,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 251,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    71 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of function count expects array|Countable, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'line' => 261,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 261,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method App\\Http\\Controllers\\DailyPlanningController::middleware().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'line' => 19,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 19,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\DailyPlanningController::generate() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'line' => 29,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 29,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::user().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 31,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\DailyPlanningController::updateTasks() has no return type specified.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Contracts\\Auth\\Factory::user().',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 39,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Services\\DailyPlanningService extends unknown class App\\Services\\BaseService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 14,
       'canBeIgnored' => false,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 14,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Class_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant App\\Services\\DailyPlanningService::BUFFER_DURATION is unused.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/developing-extensions/always-used-class-constants',
       'nodeLine' => 14,
       'nodeType' => 'PHPStan\\Node\\ClassConstantsNode',
       'identifier' => 'classConstant.unused',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::generateDailyPlanning() has parameter $options with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 28,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::generateDailyPlanning() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 28,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::getTodayTasks() return type with generic class Illuminate\\Support\\Collection does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 71,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 87,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 88,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 89,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 90,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $projectId of method App\\Services\\DailyPlanningService::getProjectName() expects string|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 90,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $todoistPriority of method App\\Services\\DailyPlanningService::mapPriority() expects int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $task of method App\\Services\\DailyPlanningService::extractEnergy() expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 92,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 92,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $task of method App\\Services\\DailyPlanningService::extractScheduledTime() expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 93,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 93,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $task of method App\\Services\\DailyPlanningService::extractDuration() expects array, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'description\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 95,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 95,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'labels\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 96,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 96,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::prioritizeTasks() has parameter $tasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 101,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 101,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::prioritizeTasks() return type with generic class Illuminate\\Support\\Collection does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 101,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 101,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 105,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 105,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 105,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 105,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 108,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 108,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 113,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 113,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $string of function strtolower expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 113,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 113,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 114,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $string of function strtolower expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 114,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 125,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 125,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'energy\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 131,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 131,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'energy\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 131,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 131,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 137,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 137,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 145,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 145,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 145,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 145,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 148,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 148,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 148,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 148,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::identifyMIT() has parameter $tasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 156,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 156,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::identifyMIT() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 156,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 156,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::identifyMIT() should return array|null but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 159,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 159,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'priority\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'energy\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 161,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 161,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::createTimeBlocks() has parameter $mit with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 165,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::createTimeBlocks() has parameter $tasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 165,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::createTimeBlocks() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 165,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $duration of method App\\Services\\DailyPlanningService::createBlock() expects int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 177,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 177,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #4 $taskId of method App\\Services\\DailyPlanningService::createBlock() expects string|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 177,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 177,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Part $mit[\'content\'] (mixed) of encapsed string cannot be cast to string.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 177,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 177,
       'nodeType' => 'PhpParser\\Node\\Scalar\\InterpolatedString',
       'identifier' => 'encapsedStringPart.nonString',
       'metadata' => 
      array (
      ),
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of method Carbon\\Carbon::addMinutes() expects float|int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 188,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 188,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    53 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 202,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 202,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    54 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $time of static method Carbon\\Carbon::parse() expects Carbon\\Month|Carbon\\WeekDay|DateTimeInterface|float|int|string|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 203,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    55 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $duration of method App\\Services\\DailyPlanningService::createBlock() expects int, float given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 208,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 208,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    56 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 214,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 214,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    57 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 214,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 214,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    58 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $duration of method App\\Services\\DailyPlanningService::createBlock() expects int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 214,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 214,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    59 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #3 $title of method App\\Services\\DailyPlanningService::createBlock() expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 214,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 214,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    60 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #4 $taskId of method App\\Services\\DailyPlanningService::createBlock() expects string|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 214,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 214,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    61 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of method Carbon\\Carbon::addMinutes() expects float|int, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 215,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 215,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    62 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::createBlock() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 234,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 234,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    63 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::checkAlerts() has parameter $allTasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 248,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    64 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::checkAlerts() has parameter $topTasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 248,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    65 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::checkAlerts() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 248,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    66 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 263,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 263,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    67 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 275,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 275,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    68 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::findTimeConflicts() has parameter $scheduledTasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 287,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 287,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    69 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::findTimeConflicts() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 287,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 287,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    70 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 297,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 297,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    71 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'scheduled_time\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 297,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 297,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    72 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 299,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 299,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    73 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 300,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 300,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    74 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::generateSummary() has parameter $timeBlocks with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 310,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 310,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    75 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::generateSummary() has parameter $topTasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 310,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 310,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    76 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::generateSummary() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 310,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 310,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    77 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 313,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 313,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    78 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'title\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 317,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 317,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    79 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'title\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 317,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 317,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    80 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $haystack of function str_contains expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 317,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 317,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    81 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $haystack of function str_contains expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 317,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 317,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    82 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'project_name\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    83 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $string of function strtolower expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    84 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::prepareTodoistUpdates() has parameter $tasks with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 329,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 329,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    85 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::prepareTodoistUpdates() has parameter $timeBlocks with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 329,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 329,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    86 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::prepareTodoistUpdates() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 329,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 329,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    87 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'task_id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 338,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 338,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    88 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 341,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 341,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    89 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 346,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 346,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    90 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 347,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 347,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    91 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'start\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 348,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 348,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    92 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 352,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 352,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    93 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 354,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 354,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    94 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 355,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 355,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    95 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'duration\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 356,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 356,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    96 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'id\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 363,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 363,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    97 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'content\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 364,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 364,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    98 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::extractEnergy() has parameter $task with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 382,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 382,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    99 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::extractEnergy() never returns null so it can be removed from the return type.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 382,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 382,
       'nodeType' => 'PHPStan\\Node\\MethodReturnStatementsNode',
       'identifier' => 'return.unusedType',
       'metadata' => 
      array (
      ),
    )),
    100 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Binary operation "." between mixed and \' \' results in an error.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 385,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 385,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Concat',
       'identifier' => 'binaryOp.invalid',
       'metadata' => 
      array (
      ),
    )),
    101 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $array of function implode expects array|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 385,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 385,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    102 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::extractScheduledTime() has parameter $task with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 397,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 397,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    103 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'datetime\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 400,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 400,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    104 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $time of static method Carbon\\Carbon::parse() expects Carbon\\Month|Carbon\\WeekDay|DateTimeInterface|float|int|string|null, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 401,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 401,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    105 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::extractDuration() has parameter $task with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 407,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 407,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    106 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $subject of function preg_match expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 412,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 412,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    107 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $subject of function preg_match expects string, mixed given.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 416,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 416,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    108 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::list() has parameter $filters with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 434,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 434,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    109 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::list() return type with generic class Illuminate\\Pagination\\LengthAwarePaginator does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 434,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 434,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    110 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::create() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 446,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 446,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    111 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\DailyPlanningService::update() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'line' => 452,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 452,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Services\\TodoistService extends unknown class App\\Services\\BaseService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 16,
       'canBeIgnored' => false,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 16,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Class_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $mcpProxy of method App\\Services\\TodoistService::__construct() has invalid type App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 21,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property App\\Services\\TodoistService::$mcpProxy has unknown class App\\Services\\McpProxyService as its type.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 21,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 21,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTodayTasks() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 31,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 35,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 35,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTodayTasks() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTomorrowTasks() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 40,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 40,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 44,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 46,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTomorrowTasks() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 46,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getWeekTasks() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 49,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getWeekTasks() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTask() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 58,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 62,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 62,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getTask() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::createTask() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 69,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::createTask() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 69,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 73,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 75,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 75,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::createTask() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 75,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 75,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::updateTask() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 78,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 78,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::updateTask() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 78,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 78,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 82,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 86,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 86,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::updateTask() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 86,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 86,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 110,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 110,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getProjects() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 121,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 121,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 125,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 127,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 127,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getProjects() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 127,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 127,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getProject() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 130,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 130,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 134,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 134,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::getProject() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::bulkUpdateTasks() has parameter $taskIds with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 141,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 141,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::bulkUpdateTasks() has parameter $updates with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 141,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 141,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::bulkUpdateTasks() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 141,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 141,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 145,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 145,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 149,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::bulkUpdateTasks() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 149,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::quickAddTask() return type has no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 152,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 152,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method request() on an unknown class App\\Services\\McpProxyService.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 156,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 156,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot access offset \'data\' on mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrayDimFetch',
       'identifier' => 'offsetAccess.nonOffsetAccessible',
       'metadata' => 
      array (
      ),
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::quickAddTask() should return array but returns mixed.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::list() has parameter $filters with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 182,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 182,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::list() return type with generic class Illuminate\\Pagination\\LengthAwarePaginator does not specify its types: TKey, TValue',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 182,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 182,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::create() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 192,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 192,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TodoistService::update() has parameter $data with no value type specified in iterable type array.',
       'file' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'line' => 197,
       'canBeIgnored' => true,
       'filePath' => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 197,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Actions\\ActionResult',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Actions\\DailyPlanning\\CreateDailyPlanningAction',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction',
    ),
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction',
        1 => 'determineUpdates',
        2 => 'App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction',
      ),
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Services\\DailyPlanningService',
    ),
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Services\\DailyPlanningService',
        1 => 'mapPriority',
        2 => 'App\\Services\\DailyPlanningService',
      ),
      1 => 
      array (
        0 => 'App\\Services\\DailyPlanningService',
        1 => 'getProjectName',
        2 => 'App\\Services\\DailyPlanningService',
      ),
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Services\\TodoistService',
    ),
  ),
); },
	'dependencies' => array (
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php' => 
  array (
    'fileHash' => 'ef35dccbb1d23c3da2c3d9933d1b4d8421c21f7e',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php',
      1 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
      2 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
      3 => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php' => 
  array (
    'fileHash' => 'b8e3f1bd3b1e95065a1a2c180ed0fa6c717eff75',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
      1 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
      2 => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php' => 
  array (
    'fileHash' => '16f20f331b9adbfb78530b5090c21384787e2a7e',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php' => 
  array (
    'fileHash' => 'd7f5d5d3c59af944d8fee5836e6525b73847b793',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php' => 
  array (
    'fileHash' => '91cbfd08e0b0fee99f9d865ef7d2eba1b36c3f13',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php' => 
  array (
    'fileHash' => '26721833af672a8779b8aab05a6f8974bc49d379',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php',
    ),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php' => 
  array (
    'fileHash' => 'aa55fb3537985e1aac931e5b1473dc283bd114c2',
    'dependentFiles' => 
    array (
      0 => '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php',
      1 => '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php',
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/ActionResult.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Actions\\ActionResult',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'success',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'error',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'errors',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'statusCode',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'validationError',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'errors',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'unauthorized',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toResponse',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/BaseAction.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Actions\\BaseAction',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Execute the action with transaction handling
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Actions\\ActionResult',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'validate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Validate input data
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check authorization
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'execute',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Main business logic
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'beforeExecute',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Hook before execution (optional)
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'afterExecute',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Hook after execution (optional)
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'result',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleError',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Custom error handling
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Actions\\ActionResult',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => 'Exception',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logSuccess',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log successful execution
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'result',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logError',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log error
     */',
             'namespace' => 'App\\Actions',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => 'Exception',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/CreateDailyPlanningAction.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Actions\\DailyPlanning\\CreateDailyPlanningAction',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Actions\\BaseAction',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'planningService',
               'type' => 'App\\Services\\DailyPlanningService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'validate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'execute',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'afterExecute',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'result',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleError',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Actions\\ActionResult',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => 'Exception',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Actions/DailyPlanning/UpdateTodoistTasksAction.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Actions\\BaseAction',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'todoist',
               'type' => 'App\\Services\\TodoistService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'validate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'execute',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'parameters',
               'type' => NULL,
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'afterExecute',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'result',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleError',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Actions\\ActionResult',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => 'Exception',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/DailyPlanningController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\DailyPlanningController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'App\\Actions\\DailyPlanning\\CreateDailyPlanningAction',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateTasks',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'App\\Actions\\DailyPlanning\\UpdateTodoistTasksAction',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/DailyPlanningService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\DailyPlanningService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Services\\BaseService',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'todoist',
               'type' => 'App\\Services\\TodoistService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateDailyPlanning',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'options',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'list',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Pagination\\LengthAwarePaginator',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'filters',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'find',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/fred/PhpstormProjects/mcp_manager/app/Services/TodoistService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\TodoistService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Services\\BaseService',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'mcpProxy',
               'type' => 'App\\Services\\McpProxyService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setUser',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTodayTasks',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTomorrowTasks',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWeekTasks',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'taskId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'createTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'taskId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'completeTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'taskId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deleteTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'taskId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getProjects',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getProject',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'projectId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'bulkUpdateTasks',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'taskIds',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'updates',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'quickAddTask',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'text',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'list',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Pagination\\LengthAwarePaginator',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'filters',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'find',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Model',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'int|string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
