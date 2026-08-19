<form method="POST" action="/login">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <div class="form-group">
        <label>Email or Username</label>
        <input type="text" name="identifier" required autofocus>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <label style="font-size:.85rem;color:var(--text2);display:flex;align-items:center;gap:.4rem">
            <input type="checkbox" name="remember" value="1" style="width:auto"> Remember me
        </label>
        <a href="/forgot-password" style="font-size:.85rem">Forgot password?</a>
    </div>
    <button type="submit" class="btn">Login</button>
    <div class="links">Don't have an account? <a href="/register">Sign up</a></div>
</form>
