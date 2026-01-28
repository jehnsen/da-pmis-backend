# Password Reset Implementation Guide

## Overview

A complete password reset system has been implemented for the DA-PMIS API with email notifications, rate limiting, and security features.

## API Endpoints

### 1. Request Password Reset (Forgot Password)
**Endpoint:** `POST /api/password/forgot`
**Rate Limit:** 3 requests per minute
**Authentication:** Not required

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
  "message": "Password reset link sent to your email!"
}
```

**Error Response (422):**
```json
{
  "errors": {
    "email": ["The email field is required."]
  }
}
```

**Error Response (500):**
```json
{
  "error": "Unable to send reset link. Please try again."
}
```

---

### 2. Reset Password with Token
**Endpoint:** `POST /api/password/reset`
**Rate Limit:** 5 requests per minute
**Authentication:** Not required

**Request Body:**
```json
{
  "token": "reset-token-from-email",
  "email": "user@example.com",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Success Response (200):**
```json
{
  "message": "Password reset successfully!"
}
```

**Error Response (400):**
```json
{
  "error": "Invalid or expired token."
}
```

**Error Response (422):**
```json
{
  "errors": {
    "password": ["The password must be at least 6 characters."],
    "password_confirmation": ["The password confirmation does not match."]
  }
}
```

---

### 3. Change Password (Authenticated Users)
**Endpoint:** `POST /api/password/change`
**Authentication:** Required (Bearer Token)

**Request Body:**
```json
{
  "current_password": "OldPassword123!",
  "new_password": "NewPassword456!",
  "new_password_confirmation": "NewPassword456!"
}
```

**Success Response (200):**
```json
{
  "message": "Password changed successfully!"
}
```

**Error Response (400):**
```json
{
  "error": "Current password is incorrect."
}
```

---

## Implementation Details

### Database
- **Table:** `password_reset_tokens`
- **Structure:**
  - `email` (primary key)
  - `token` (hashed)
  - `created_at`

### Email Notification
- Custom notification: `App\Notifications\ResetPasswordNotification`
- Sends email with reset link to frontend
- Reset link format: `{FRONTEND_URL}/reset-password?token={token}&email={email}`
- Token expiration: 60 minutes (configurable in `config/auth.php`)

### Security Features

1. **Rate Limiting:**
   - Forgot password: 3 attempts/minute
   - Reset password: 5 attempts/minute
   - Prevents brute force attacks

2. **Token Validation:**
   - Tokens expire after 60 minutes
   - One-time use only
   - Hashed in database

3. **Password Requirements:**
   - Minimum 6 characters
   - Must match confirmation
   - Hashed using bcrypt

---

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Frontend Application URL (for password reset links)
FRONTEND_URL=http://localhost:3000

# Mail Configuration (currently using log driver for testing)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@da-pmis.gov.ph
MAIL_FROM_NAME="${APP_NAME}"

# Password Reset Token Expiration (in minutes)
# Configured in config/auth.php - default is 60 minutes
```

### Mail Driver Options

For production, update mail configuration:

```env
# SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@da-pmis.gov.ph
MAIL_FROM_NAME="DA-PMIS CARAGA"
```

---

## Frontend Integration

### Step 1: Request Password Reset
```javascript
const forgotPassword = async (email) => {
  try {
    const response = await fetch('http://localhost:8000/api/password/forgot', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email }),
    });

    const data = await response.json();

    if (response.ok) {
      alert('Password reset link sent to your email!');
    } else {
      alert(data.error || 'Failed to send reset link');
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

### Step 2: Reset Password Page
Create a page at `/reset-password` that:
1. Extracts `token` and `email` from URL query parameters
2. Shows a form with password and password confirmation fields
3. Submits to `/api/password/reset`

```javascript
const resetPassword = async (token, email, password, passwordConfirmation) => {
  try {
    const response = await fetch('http://localhost:8000/api/password/reset', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      alert('Password reset successfully! Please login.');
      // Redirect to login page
      window.location.href = '/login';
    } else {
      alert(data.error || 'Failed to reset password');
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

### Step 3: Change Password (Authenticated)
```javascript
const changePassword = async (currentPassword, newPassword, newPasswordConfirmation) => {
  try {
    const token = localStorage.getItem('auth_token'); // Your auth token

    const response = await fetch('http://localhost:8000/api/password/change', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      alert('Password changed successfully!');
    } else {
      alert(data.error || 'Failed to change password');
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## Testing

### Test Password Reset Flow

1. **Request reset link:**
```bash
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@da-pmis.gov.ph"}'
```

2. **Check logs for reset token:**
```bash
tail -f storage/logs/laravel.log
```

3. **Extract token from log and reset password:**
```bash
curl -X POST http://localhost:8000/api/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "token":"your-token-from-email",
    "email":"admin@da-pmis.gov.ph",
    "password":"NewPassword123!",
    "password_confirmation":"NewPassword123!"
  }'
```

### Test Rate Limiting

Make 4+ consecutive requests to see throttling:
```bash
for i in {1..4}; do
  curl -X POST http://localhost:8000/api/password/forgot \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@da-pmis.gov.ph"}'
  echo ""
done
```

---

## Migration

The password reset tokens table migration:
```bash
php artisan migrate
```

**Migration file:** `database/migrations/2026_01_28_021653_create_password_reset_tokens_table.php`

---

## Troubleshooting

### Email not sending
- Check `MAIL_MAILER` is set in `.env`
- For testing, use `MAIL_MAILER=log` and check `storage/logs/laravel.log`
- For production, configure SMTP settings

### Invalid or expired token
- Tokens expire after 60 minutes
- Each token can only be used once
- Check that email matches the one used to request reset

### Frontend URL incorrect in email
- Update `FRONTEND_URL` in `.env`
- Default is `http://localhost:3000`
- Should point to your frontend application

### Rate limit errors
- Wait 1 minute before retrying
- Limits are per IP address
- Forgot password: 3/min, Reset: 5/min

---

## Security Considerations

1. **Never expose tokens in logs** - Tokens are hashed in database
2. **Use HTTPS in production** - Prevents token interception
3. **Set strong SMTP credentials** - Use app-specific passwords
4. **Monitor failed attempts** - Check audit logs for suspicious activity
5. **Configure frontend CORS** - Only allow trusted domains

---

## Related Files

- **Controller:** `app/Http/Controllers/AuthController.php`
- **Notification:** `app/Notifications/ResetPasswordNotification.php`
- **User Model:** `app/Models/User.php`
- **Routes:** `routes/api.php`
- **Config:** `config/auth.php`, `config/app.php`
- **Migration:** `database/migrations/2026_01_28_021653_create_password_reset_tokens_table.php`

---

**Version:** 1.0
**Updated:** 2026-01-28
**Status:** ✅ Implemented and Ready for Use
