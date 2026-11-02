document.addEventListener('DOMContentLoaded', function () {
'use strict';
var printButton = document.getElementById('printInvoice');
var downloadButton = document.getElementById('downloadInvoice');
if (printButton) printButton.addEventListener('click', function () { window.print(); });
if (downloadButton) downloadButton.addEventListener('click', function () { window.print(); });
});