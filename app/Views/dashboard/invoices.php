<?php $pageTitle = 'Invoices'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Invoices</h1>
<div class="card">
<?php if (empty($orders)): ?>
<p style="color:var(--text2)">No invoices yet.</p>
<?php else: ?>
<table>
    <thead><tr><th>Invoice</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr><td><?= e($o->order_number) ?></td><td><?= e($o->product()?->name ?? '-') ?></td><td><?= '$' . number_format($o->amount, 2) ?></td><td><span class="badge <?= $o->status === 'paid' ? 'badge-ok' : 'badge-warn' ?>"><?= e(ucfirst($o->status)) ?></span></td><td><?= e($o->created_at) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
