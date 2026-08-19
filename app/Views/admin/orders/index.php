<?php $pageTitle = 'Admin - Orders'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Orders</h1>
<div class="card">
<table>
<thead><tr><th>Order #</th><th>User</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
    <td style="font-weight:600"><?= e($o['order']->order_number) ?></td>
    <td><?= e($o['user']?->username ?? '-') ?></td>
    <td><?= e($o['product']?->name ?? '-') ?></td>
    <td><?= '$' . number_format($o['order']->amount, 2) ?></td>
    <td><span class="badge <?= $o['order']->status === 'paid' ? 'badge-ok' : ($o['order']->status === 'pending' ? 'badge-warn' : 'badge-err') ?>"><?= e(ucfirst($o['order']->status)) ?></span></td>
    <td style="color:var(--text2)"><?= timeAgo($o['order']->created_at) ?></td>
    <td>
        <?php if ($o['order']->status === 'pending'): ?>
        <form method="POST" action="/admin/orders/<?= $o['order']->id ?>/update" style="display:inline">
            <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
            <input type="hidden" name="status" value="paid">
            <button class="btn btn-ok btn-sm">Mark Paid</button>
        </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
