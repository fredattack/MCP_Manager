import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Setup global exécuté une fois avant tous les tests E2E
 */
async function globalSetup() {
  console.log('🚀 E2E Global setup started...');

  try {
    // Vérifier que la base de données PostgreSQL de test existe
    console.log('📦 Checking PostgreSQL test database...');

    // Créer la base de données de test si elle n'existe pas
    await execAsync('psql -U postgres -tc "SELECT 1 FROM pg_database WHERE datname = \'mcp_manager_test\'" | grep -q 1 || psql -U postgres -c "CREATE DATABASE mcp_manager_test"').catch(() => {
      console.log('⚠️  Database creation skipped (may already exist or psql not accessible)');
    });

    // Préparer la base de données de test avec migrations et seeders
    console.log('🔄 Running migrations and seeders...');
    await execAsync('php artisan migrate:fresh --seed --env=testing');
    console.log('✅ Database migrated and seeded');

    // Le seeder crée déjà un utilisateur par défaut (info@hddev.be / password)
    console.log('✅ Test user available from seeder (info@hddev.be / password)');

  } catch (error) {
    console.error('❌ E2E Global setup failed:', error);
    throw error;
  }

  console.log('✅ E2E Global setup completed');
}

export default globalSetup;
