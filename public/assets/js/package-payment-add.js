document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var currency = document.getElementById('currency');
    var saleAmount = document.getElementById('saleAmount');
    var receivedAmount = document.getElementById('receivedAmount');
    var customerStatus = document.getElementById('customerPaymentStatus');
    var supplierPaid = document.getElementById('supplierPaid');
    var supplierStatus = document.getElementById('supplierPaymentStatus');
    var agentCommission = document.getElementById('agentCommission');
    var otherExpense = document.getElementById('otherExpense');
    var costInputs = Array.prototype.slice.call(document.querySelectorAll('.cost-input'));
    var form = document.getElementById('packagePaymentForm');

    function numberValue(element) {
        return parseFloat(element.value) || 0;
    }

    function money(amount) {
        return currency.value + ' ' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function calculateFinance() {
        var sale = numberValue(saleAmount);
        var cost = costInputs.reduce(function (total, input) {
            return total + numberValue(input);
        }, 0);
        var expenses = numberValue(agentCommission) + numberValue(otherExpense);
        var profit = sale - cost - expenses;
        var margin = sale > 0 ? (profit / sale) * 100 : 0;
        var profitBox = document.querySelector('.profit-box');

        document.getElementById('totalPackageCost').textContent = money(cost);
        document.getElementById('totalPackageCostInput').value = cost.toFixed(2);
        document.getElementById('previewSale').textContent = money(sale);
        document.getElementById('previewCost').textContent = money(cost);
        document.getElementById('previewExpense').textContent = money(expenses);
        document.getElementById('previewProfit').textContent = money(profit);
        document.getElementById('profitMargin').textContent = margin.toFixed(2) + '% margin';
        document.getElementById('netProfitInput').value = profit.toFixed(2);
        profitBox.classList.toggle('loss', profit < 0);

        var received = numberValue(receivedAmount);
        if (sale > 0 && received >= sale) customerStatus.value = 'Paid';
        else if (received > 0) customerStatus.value = 'Partially Paid';
        else customerStatus.value = 'Unpaid';

        var paid = numberValue(supplierPaid);
        if (cost > 0 && paid >= cost) supplierStatus.value = 'Paid';
        else if (paid > 0) supplierStatus.value = 'Partially Paid';
        else supplierStatus.value = 'Unpaid';
    }

    document.querySelectorAll('.finance-input, .cost-input').forEach(function (input) {
        input.addEventListener('input', calculateFinance);
        input.addEventListener('change', calculateFinance);
    });

    document.getElementById('travelTo').addEventListener('change', function () {
        var from = document.getElementById('travelFrom').value;
        if (from && this.value && this.value < from) {
            alert('Travel To date cannot be earlier than Travel From date.');
            this.value = '';
        }
    });

    form.addEventListener('submit', function (event) {
        calculateFinance();
        if (numberValue(saleAmount) <= 0) {
            event.preventDefault();
            alert('Please enter a valid Package Sale Amount.');
            saleAmount.focus();
        }
    });

    form.addEventListener('reset', function () {
        window.setTimeout(calculateFinance, 0);
    });

    calculateFinance();
});