<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WireGuardReload extends Command
{
    protected $signature = 'wireguard:reload {interface=wg0}';

    protected $description = 'Safely reload WireGuard configuration using wg-quick strip + wg syncconf';

    public function handle(): int
    {
        $iface = $this->argument('interface');

        // 1) Generate a stripped config into a secure temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'wgstrip_');
        if ($tmpFile === false) {
            $this->error('Failed to create temp file');
            return self::FAILURE;
        }

        try {
            $stripCmd = [
                '/usr/bin/sudo', '/usr/bin/wg-quick', 'strip', $iface,
            ];
            $process = new Process($stripCmd);
            $process->setTimeout(30);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $output = $process->getOutput();

            if (file_put_contents($tmpFile, $output) === false) {
                $this->error('Failed to write stripped config to temp file');
                return self::FAILURE;
            }

            // 2) Apply with wg syncconf using the temp file path
            $syncCmd = [
                '/usr/bin/sudo', '/usr/bin/wg', 'syncconf', $iface, $tmpFile,
            ];
            $sync = new Process($syncCmd);
            $sync->setTimeout(30);
            $sync->run();
            if (!$sync->isSuccessful()) {
                throw new ProcessFailedException($sync);
            }

            $this->info("WireGuard interface '{$iface}' reloaded successfully.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('WireGuard reload failed: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        }
    }
}
