document.addEventListener('DOMContentLoaded', function () {
    //Print Invoice
    const printButton = document.getElementById('printInvoice');
    if (printButton) {
        printButton.addEventListener('click', function () {
            window.print();
        });
    }
    //Download PDF
    const downloadButton = document.getElementById('downloadInvoice');
    if (downloadButton) {
        downloadButton.addEventListener('click', function () {
            const invoice = document.getElementById('invoiceDocument');
            if (!invoice) {
                return;
            }
            if (typeof html2pdf === 'undefined') {
                alert('PDF library load nahi hui.');
                return;
            }
            const options = {
                margin: 0,
                filename: 'wallet-topup-invoice.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf()
                .set(options)
                .from(invoice)
                .save();
        });
    }
});