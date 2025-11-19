# Troubleshooting: "This site can't be reached" Error

## Problem
Browser shows "This site can't be reached - ERR_CONNECTION_CLOSED" error when accessing `/screens` page.
Terminal shows: `Invalid request (Unsupported SSL request)`

## Root Cause
The browser is attempting to connect via HTTPS, but `php artisan serve` only supports HTTP connections.

## Solutions

### Solution 1: Use HTTP Protocol Explicitly
Access the page using:
```
http://beta.localhost:8000/screens
```

### Solution 2: Clear Browser HSTS Cache

**For Chrome/Edge:**
1. Navigate to: `chrome://net-internals/#hsts` (or `edge://net-internals/#hsts`)
2. Scroll to "Delete domain security policies"
3. Enter: `beta.localhost`
4. Click "Delete"
5. Restart browser

**For Firefox:**
1. Close Firefox completely
2. Navigate to your Firefox profile folder:
   - Windows: `%APPDATA%\Mozilla\Firefox\Profiles\`
3. Delete the file: `SiteSecurityServiceState.txt`
4. Restart Firefox

### Solution 3: Use Incognito/Private Window
Open an incognito/private browsing window and access:
```
http://beta.localhost:8000/screens
```

### Solution 4: Configure XAMPP with SSL (Recommended for Production-like Environment)

Since you have XAMPP installed, you can configure it to handle HTTPS properly:

1. Stop the Laravel dev server
2. Configure your app in XAMPP's Apache
3. Set up SSL certificate for `beta.localhost`
4. Update your `.env` file:
   ```
   APP_URL=https://beta.localhost
   ```

## Prevention

To avoid this issue in the future:
- Always use explicit HTTP protocol when using `php artisan serve`
- For HTTPS development, use Laravel Valet, Homestead, or configure XAMPP with SSL
- Don't mix HTTP and HTTPS in the same development session
