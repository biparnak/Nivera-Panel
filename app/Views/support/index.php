<?php $pageTitle = 'Support'; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:700">Support Tickets</h1>
    <a href="/support/create" class="btn btn-accent btn-sm">New Ticket</a>
</div>
<?php if (empty($tickets)): ?>
<div class="card empty">No tickets yet. <a href="/support/create">Create one</a> if you need help.</div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>#</th><th>Subject</th><th>Status</th><th>Priority</th><th>Last Reply</th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr style="cursor:pointer" onclick="location.href='/support/<?= $t->id ?>'">
            <td style="font-weight:600"><?= e($t->ticket_number) ?></td>
            <td><?= e($t->subject) ?></td>
            <td><span class="badge <?= $t->status === 'open' ? 'badge-ok' : ($t->status === 'replied' ? 'badge-info' : 'badge-err') ?>"><?= e(ucfirst($t->status)) ?></span></td>
            <td><?= e(ucfirst($t->priority)) ?></td>
            <td style="color:var(--text2)"><?= $t->updated_at ? timeAgo($t->updated_at) : '-' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
