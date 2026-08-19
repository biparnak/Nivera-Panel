<form method="POST" action="/forgot-password">
    <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
    <p style="color:var(--text2);margin-bottom:1rem;font-size:.9rem">Enter your email and we'll send you a reset link.</p>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>
    <button type="submit" class="btn">Send Reset Link</button>
    <div class="links"><a href="/login">Back to login</a></div>
</form>
