# MikroTik Status Check Scheduler Setup

## ✅ Command Already Registered

The `mikrotik:check-status` command is already registered in `app/Console/Kernel.php` and will run every 3 minutes.

## 🧪 Testing the Command

### Test the command manually:
```bash
php artisan mikrotik:check-status
```

This will:
- Check all routers with IP addresses
- Mark routers offline if `last_seen_at` is > 4 minutes old
- Test API connections to each router
- Update router status in the database

## ⚙️ Setting Up Laravel Scheduler

Laravel's scheduler needs to run via a cron job (Linux) or Task Scheduler (Windows).

### For Linux/Unix Servers:

Add this to your crontab (run `crontab -e`):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with your actual project path, for example:
```bash
* * * * * cd /var/www/zyra && php artisan schedule:run >> /dev/null 2>&1
```

### For Windows Servers:

1. Open **Task Scheduler** (search for it in Start menu)
2. Click **Create Basic Task**
3. Name it: "Laravel Scheduler"
4. Trigger: **Daily** (then set to repeat every minute)
5. Action: **Start a program**
6. Program: `php.exe`
7. Arguments: `artisan schedule:run`
8. Start in: `C:\Users\LDNS NETWORKS\Documents\zyra` (your project path)

Or use PowerShell to create it:
```powershell
$action = New-ScheduledTaskAction -Execute "php.exe" -Argument "artisan schedule:run" -WorkingDirectory "C:\Users\LDNS NETWORKS\Documents\zyra"
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 365)
Register-ScheduledTask -TaskName "Laravel Scheduler" -Action $action -Trigger $trigger -Description "Runs Laravel scheduled tasks"
```

## 📋 Verify Scheduler is Running

### Check scheduled tasks:
```bash
php artisan schedule:list
```

This shows all scheduled commands and when they'll run next.

### Run scheduler manually (for testing):
```bash
php artisan schedule:run
```

## 🔍 Monitoring

### Check logs:
The command logs to `storage/logs/laravel.log`. You can monitor it:
```bash
tail -f storage/logs/laravel.log | grep "MikroTik"
```

### Check command output:
The command provides output showing:
- How many routers were checked
- How many are online/offline
- How many were marked stale (>4 minutes)

## 🎯 What Happens

Every 3 minutes, the scheduler will:
1. Run `mikrotik:check-status`
2. Check all routers with IP addresses
3. Mark routers offline if `last_seen_at` > 4 minutes
4. Test API connections
5. Update router status in database

## 🚨 Troubleshooting

### Command not found?
Make sure the file exists: `app/Console/Commands/CheckMikrotikStatus.php`

### Scheduler not running?
- Verify cron job is active: `crontab -l` (Linux)
- Check Task Scheduler is running (Windows)
- Check Laravel logs for errors

### Command runs but routers stay online?
- Check router `last_seen_at` timestamps
- Verify phone-home scheduler is working on routers
- Check API connectivity from server to routers

