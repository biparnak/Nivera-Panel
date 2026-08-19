<?php $pageTitle = 'Ticket ' . e($ticket->ticket_number); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <div><h1 style="font-size:1.3rem;font-weight:700"><?= e($ticket->subject) ?></h1><span style="color:var(--text2);font-size:.85rem">Ticket <?= e($ticket->ticket_number) ?> &middot; <span class="badge <?= $ticket->status === 'open' ? 'badge-ok' : 'badge-err' ?>"><?= e(ucfirst($ticket->status)) ?></span></span></div>
    <a href="/support" class="btn btn-sm" style="background:transparent;color:var(--text);border:1px solid var(--border)">Back</a>
</div>

<div class="card" style="margin-bottom:1rem">
<?php foreach ($messages as $msg): ?>
<div style="padding:1rem 0;<?= !$msg->is_staff ? 'border-left:3px solid var(--accent);padding-left:1rem' : 'border-left:3px solid var(--ok);padding-left:1rem;opacity:.9' ?>">
    <div style="display:flex;justify-content:space-between;margin-bottom:.5rem">
        <span style="font-weight:600;font-size:.9rem"><?= $msg->is_staff ? 'Staff' : e($_user->username) ?></span>
        <span style="color:var(--text2);font-size:.8rem"><?= timeAgo($msg->created_at) ?></span>
    </div>
    <p style="font-size:.9rem;line-height:1.6"><?= nl2br(e($msg->message)) ?></p>
</div>
<hr style="border:none;border-top:1px solid var(--border)">
<?php endforeach; ?>
</div>

<?php if ($ticket->status !== 'closed'): ?>
<div class="card">
    <form method="POST" action="/support/<?= $ticket->id ?>/reply">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group"><textarea name="message" required placeholder="Type your reply..."></textarea></div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-accent">Send Reply</button>
            <form method="POST" action="/support/<?= $ticket->id ?>/close" style="display:inline"><input type="hidden" name="_token" value="<?= e($_csrf) ?>"><button class="btn btn-err btn-sm">Close Ticket</button></form>
        </div>
    </form>
</div>
<?php endif; ?>
