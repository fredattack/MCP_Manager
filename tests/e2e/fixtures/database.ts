import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Nettoyer la base de données sans migrate:fresh (plus rapide)
 * Utilise db:wipe pour supprimer les données mais garder la structure
 */
export async function cleanDatabase(): Promise<void> {
  await execAsync('php artisan db:wipe --env=testing --force');
  await execAsync('php artisan migrate --env=testing --force');
  await execAsync('php artisan db:seed --env=testing --force');
}

/**
 * Réinitialiser complètement la base de données (plus lent, à éviter)
 * Utilisé uniquement si cleanDatabase() ne suffit pas
 */
export async function resetDatabase(): Promise<void> {
  await execAsync('php artisan migrate:fresh --seed --env=testing --force');
}

/**
 * Créer un utilisateur de test
 */
export async function createUser(
  email: string = 'user@example.com',
  password: string = 'password'
): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    \\App\\Models\\User::factory()->create([
      'email' => '${email}',
      'password' => bcrypt('${password}')
    ]);
  " --env=testing`);
}

/**
 * Créer des workflows de test
 */
export async function seedWorkflows(count: number = 5): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    \\App\\Models\\Workflow::factory()->count(${count})->create();
  " --env=testing`);
}

/**
 * Nettoyer une table spécifique
 */
export async function truncateTable(table: string): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    DB::table('${table}')->truncate();
  " --env=testing`);
}
