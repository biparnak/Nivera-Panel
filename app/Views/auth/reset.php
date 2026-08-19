<form method="POST" action="/reset-password">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="form-group">
        <label>New Password</label>
        <input type="password" name="password" required minlength="8">
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn">Reset Password</button>
</form>
