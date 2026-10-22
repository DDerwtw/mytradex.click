@php
    $firstPackage = $packages[0];
    $firstDailyEstimate = $firstPackage['amount'] * $firstPackage['daily_rate'] / 100;
    $firstRewardEstimate = min($firstDailyEstimate * 30, $firstPackage['amount'] * ($payoutCapMultiple - 1));
    $firstTotalEstimate = $firstPackage['amount'] + $firstRewardEstimate;
    $firstMinimumWithdrawal = $firstPackage['minimum_withdrawal'];
@endphp
<section class="dashboard-panel model-calculator">
    <div class="dashboard-panel-heading"><div><div class="eyebrow">Interactive estimate</div><h2>Investment maturity calculator</h2></div><span class="status-pill">Preview only</span></div>
    <p class="dashboard-muted model-calc-intro">Choose a package and a period from 30 to 180 days to see an illustrative estimate using its daily-rate assumption.</p>
    <div class="model-calc-controls">
        <label for="preview-package">Package
            <select id="preview-package">
                @foreach ($packages as $index => $package)
                <option value="{{ $index }}" data-package="{{ $package['name'] }}" data-amount="{{ $package['amount'] }}" data-rate="{{ $package['daily_rate'] }}" data-minimum-withdrawal="{{ $package['minimum_withdrawal'] }}">{{ $package['name'] }} · {{ number_format($package['amount']) }} USDT · {{ number_format($package['daily_rate'], 1) }}% daily</option>
                @endforeach
            </select>
        </label>
        <label for="preview-period">Maturity period <strong><span id="preview-period-value">30</span> days</strong>
            <input id="preview-period" type="range" min="30" max="180" step="1" value="30" aria-describedby="preview-period-note">
            <span class="model-range-ends"><span>30 days</span><span>180 days</span></span>
        </label>
    </div>
    <div class="model-calc-results" aria-live="polite" aria-atomic="true">
        <article><span>Daily estimate</span><strong id="preview-daily">{{ number_format($firstDailyEstimate, 2) }} USDT</strong></article>
        <article><span>Estimated reward at maturity</span><strong id="preview-reward">{{ number_format($firstRewardEstimate, 2) }} USDT</strong></article>
        <article class="model-calc-total"><span>Estimated total value</span><strong id="preview-total">{{ number_format($firstTotalEstimate, 2) }} USDT</strong></article>
    </div>
    <div class="model-withdraw-status"><span>Minimum withdrawal threshold: <strong id="preview-minimum">{{ number_format($firstMinimumWithdrawal, 2) }} USDT</strong></span><b id="preview-withdraw-state">{{ $firstTotalEstimate >= $firstMinimumWithdrawal ? 'Threshold reached in this estimate' : 'Not reached in this estimate' }}</b></div>
    <p class="dashboard-muted model-calc-note" id="preview-period-note">Estimate formula: package amount × daily rate × days. Compounding is not included. No actual return is promised or paid.</p>
    @if (request()->routeIs('dashboard'))
        <form class="model-save-form" method="POST" action="{{ route('account.investments.store') }}">
            @csrf
            <input type="hidden" name="package_name" id="preview-package-name" value="{{ $firstPackage['name'] }}">
            <input type="hidden" name="maturity_days" id="preview-maturity-days" value="30">
            <button class="button button-primary" type="submit">Save this investment preview</button>
        </form>
    @endif
</section>
<script>
    (() => {
        const packageSelect = document.querySelector('#preview-package');
        const periodInput = document.querySelector('#preview-period');
        if (!packageSelect || !periodInput) return;

        const money = (value) => `${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT`;
        const updateEstimate = () => {
            const option = packageSelect.selectedOptions[0];
            const amount = Number(option.dataset.amount);
            const rate = Number(option.dataset.rate);
            const minimumWithdrawal = Number(option.dataset.minimumWithdrawal);
            const days = Number(periodInput.value);
            const daily = amount * rate / 100;
            const rewardLimit = amount * ({{ $payoutCapMultiple }} - 1);
            const reward = Math.min(daily * days, rewardLimit);
            const total = amount + reward;

            document.querySelector('#preview-period-value').textContent = days;
            const packageName = document.querySelector('#preview-package-name');
            const maturityDays = document.querySelector('#preview-maturity-days');
            if (packageName) packageName.value = option.dataset.package;
            if (maturityDays) maturityDays.value = days;
            document.querySelector('#preview-daily').textContent = money(daily);
            document.querySelector('#preview-reward').textContent = money(reward);
            document.querySelector('#preview-total').textContent = money(total);
            document.querySelector('#preview-minimum').textContent = money(minimumWithdrawal);
            document.querySelector('#preview-withdraw-state').textContent = total >= minimumWithdrawal ? 'Threshold reached in this estimate' : 'Not reached in this estimate';
        };

        packageSelect.addEventListener('change', updateEstimate);
        periodInput.addEventListener('input', updateEstimate);
        updateEstimate();
    })();
</script>
