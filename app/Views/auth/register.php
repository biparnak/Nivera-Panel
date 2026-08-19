<form method="POST" action="/register">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required minlength="3" maxlength="32">
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label>Password (min 8 characters)</label>
        <input type="password" name="password" required minlength="8">
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn">Create Account</button>
    <div class="links">Already have an account? <a href="/login">Login</a></div>
</form>
