<?php $pageTitle = 'Create Ticket'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">New Support Ticket</h1>
<div class="card" style="max-width:600px">
    <form method="POST" action="/support/create">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><label>Subject</label><input type="text" name="subject" required></div>
        <div class="form-group"><label>Department</label><select name="department"><option value="general">General</option><option value="billing">Billing</option><option value="technical">Technical</option><option value="abuse">Abuse</option></select></div>
        <div class="form-group"><label>Message</label><textarea name="message" required placeholder="Describe your issue..."></textarea></div>
        <button type="submit" class="btn btn-accent" style="width:100%">Submit Ticket</button>
    </form>
</div>
