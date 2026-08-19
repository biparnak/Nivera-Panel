<?php $pageTitle = 'Billing'; ?>
<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Billing &amp; Invoices</h1>

<div class="grid grid-3" style="margin-bottom:1.5rem">
    <div class="card stat"><div class="stat-num" style="color:var(--ok)"><?= '$' . number_format($_user->getBalance(), 2) ?></div><div class="stat-label">Balance</div></div>
    <div class="card stat"><div class="stat-num"><?= count($orders) ?></div><div class="stat-label">Total Orders</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--accent)">$0.00</div><div class="stat-label">Total Spent</div></div>
</div>

<div class="card" style="margin-bottom:1.5rem">
    <h3 style="font-weight:600;margin-bottom:1rem">Add Funds</h3>
    <form method="POST" action="/dashboard/deposit" style="display:flex;gap:1rem;align-items:end">
        <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
        <div class="form-group" style="flex:1;margin:0"><label>Amount ($)</label><input type="number" name="amount" min="1" step="0.01" required></div>
        <button type="submit" class="btn btn-accent">Deposit</button>
    </form>
</div>

<div class="card">
    <h3 style="font-weight:600;margin-bottom:1rem">Recent Orders</h3>
    <?php if (empty($orders)): ?>
    <p style="color:var(--text2)">No orders yet.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Order #</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td style="font-weight:600"><?= e($order->order_number) ?></td>
            <td><?= e($order->product()?->name ?? 'N/A') ?></td>
            <td><?= '$' . number_format($order->amount, 2) ?></td>
            <td><span class="badge <?= $order->status === 'paid' ? 'badge-ok' : ($order->status === 'pending' ? 'badge-warn' : 'badge-err') ?>"><?= e(ucfirst($order->status)) ?></span></td>
            <td style="color:var(--text2)"><?= timeAgo($order->created_at) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
