<?php $pageTitle = 'Order - ' . e($product->name); ?>
<div style="max-width:600px;margin:0 auto">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:1.5rem">Order: <?= e($product->name) ?></h1>
    <div class="card">
        <form method="POST" action="/products/<?= $product->id ?>/checkout">
            <input type="hidden" name="_token" value="<?= e($_csrf) ?>">
            <div class="form-group">
                <label>Billing Cycle</label>
                <select name="billing_cycle">
                    <option value="monthly">Monthly - <?= '$' . number_format($product->price_monthly, 2) ?></option>
                    <?php if ($product->price_yearly > 0): ?>
                    <option value="yearly">Yearly - <?= '$' . number_format($product->price_yearly, 2) ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method">
                    <option value="balance">Balance (<?= '$' . number_format($_user->getBalance(), 2) ?> available)</option>
                    <option value="manual">Manual Payment</option>
                </select>
            </div>
            <div class="form-group">
                <label>Coupon Code (optional)</label>
                <input type="text" name="coupon" placeholder="Enter coupon code">
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%">Place Order</button>
        </form>
    </div>
</div>
