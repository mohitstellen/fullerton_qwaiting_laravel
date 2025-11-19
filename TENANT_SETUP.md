# Multi-Tenant Setup Guide

## Error: "Tenant could not be identified on domain"

If you're seeing this error, it means the domain you're trying to access doesn't have a tenant configured in the database.

## Quick Fix Steps

### 1. Configure Environment Variables

Make sure your `.env` file has the `PARENT_DOMAIN` variable set:

```env
PARENT_DOMAIN=localhost
```

For production, this would be your actual domain:
```env
PARENT_DOMAIN=yourdomain.com
```

### 2. Create a Tenant and Domain

You need to create a tenant and associate a domain with it. Here are the steps:

#### Option A: Using Artisan Tinker (Recommended for Development)

```bash
php artisan tinker
```

Then run:

```php
// Create a tenant
$tenant = \App\Models\Tenant::create([
    'id' => 1,  // or any unique identifier (integer)
]);

// Create a domain for the tenant
\App\Models\Domain::create([
    'domain' => 'beta.localhost',
    'team_id' => $tenant->id,
]);
```

#### Option B: Using Database Seeder

Create a seeder file:

```bash
php artisan make:seeder TenantDomainSeeder
```

Edit `database/seeders/TenantDomainSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Domain;

class TenantDomainSeeder extends Seeder
{
    public function run()
    {
        // Create tenant (ID will auto-increment)
        $tenant = Tenant::create([]);

        // Or specify an ID
        // $tenant = Tenant::create(['id' => 1]);

        // Create domain
        Domain::create([
            'domain' => 'beta.localhost',
            'team_id' => $tenant->id,
        ]);
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=TenantDomainSeeder
```

#### Option C: Direct Database Insert

If you prefer SQL, run this in your database:

```sql
-- Insert tenant
INSERT INTO tenants (id) VALUES (1);

-- Insert domain (adjust team_id if needed)
INSERT INTO domains (domain, team_id, created_at, updated_at) 
VALUES ('beta.localhost', 1, NOW(), NOW());
```

### 3. Configure Your Hosts File (For Local Development)

For local development with subdomains, add this to your hosts file:

**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Mac/Linux:** `/etc/hosts`

```
127.0.0.1 beta.localhost
127.0.0.1 localhost
```

### 4. Restart Your Server

After making these changes, restart your development server:

```bash
php artisan serve
```

Or if using XAMPP, restart Apache.

## Common Scenarios

### Multiple Tenants

To create multiple tenants:

```php
// Tenant 1
$tenant1 = \App\Models\Tenant::create(['id' => 'company1']);
\App\Models\Domain::create(['domain' => 'company1.localhost', 'team_id' => $tenant1->id]);

// Tenant 2
$tenant2 = \App\Models\Tenant::create(['id' => 'company2']);
\App\Models\Domain::create(['domain' => 'company2.localhost', 'team_id' => $tenant2->id]);
```

### Production Setup

For production with custom domains:

```php
$tenant = \App\Models\Tenant::create(['id' => 'client-name']);
\App\Models\Domain::create([
    'domain' => 'client.yourdomain.com',
    'team_id' => $tenant->id,
]);
```

## Verify Setup

To verify your tenant is configured correctly:

```bash
php artisan tinker
```

```php
// Check if domain exists
\App\Models\Domain::where('domain', 'beta.localhost')->first();

// Check if tenant exists
\App\Models\Tenant::find('beta');
```

## Troubleshooting

1. **Clear cache after configuration changes:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

2. **Check database connection:**
   Make sure your `.env` database credentials are correct.

3. **Verify migrations:**
   Ensure tenant migrations have been run:
   ```bash
   php artisan migrate
   ```

4. **Check central_domains configuration:**
   In `config/tenancy.php`, verify the `central_domains` array includes your parent domain.

## Additional Resources

- [Tenancy for Laravel Documentation](https://tenancyforlaravel.com/docs)
- Check `config/tenancy.php` for more configuration options
