<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Создание бэкапа базы данных и удаление старых файлов';

    public function handle()
    {
        $dbName = env('DB_DATABASE','supermaker');
        $dbUser = env('DB_USERNAME','root');
        $dbPass = env('DB_PASSWORD','Desant3205363');
        $dbHost = env('DB_HOST','194.32.141.249');
        $backupDir = storage_path('app/backups/');
        $backupFile = $backupDir . now()->format('Y-m-d_H-i-s') . '_backup.sql';

        // Создаём директорию, если её нет
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        // Делаем бэкап через mysqldump
        $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > {$backupFile}";
        exec($command, $output, $resultCode);

//        if ($resultCode === 0) {
////            $this->info("Бэкап успешно создан: " . $backupFile);
//        } else {
//
////            $this->error($command);
////            $this->error("Ошибка при создании backup");
//        }

        // Удаляем старые файлы старше 14 дней
        $this->deleteOldBackups($backupDir);
    }

    private function deleteOldBackups($backupDir)
    {
        $files = glob($backupDir . '*.sql');
        $cutoff = now()->subDays(14)->timestamp;
//        $cutoff = now()->subMinutes(14)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
//                $this->info("Удалён старый бэкап: " . $file);
            }
        }
    }

}
