// Kept in its own module so block styles load after main.scss (eager globs are hoisted).
import.meta.glob('../../blocks/*/*.scss', { eager: true });
