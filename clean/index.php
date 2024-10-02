
<?php include 'header.php';?>
    <div class="container border rounded p-4">
        <h2 class="heading mb-3">Axiom Corp Contract URL Generator</h2>
        <form id="form">
            <div class="mb-3">
                <label for="brand" class="form-label">Contract Provider</label>
               <input type="text" name="contract_provider" class="form-control" id="brand" placeholder="Contract Provider">
            </div>

            <div class="mb-3">
                <label for="brand" class="form-label">Executive Sales Consultant</label>
               <input type="text" name="escName" class="form-control" id="esc" placeholder="Executive Sales Consultant">
            </div>

            <div class="mb-3">
                <label for="brand" class="form-label">Proposal Date</label>
               <input type="date" name="proposalDate" class="form-control" id="esc" placeholder="">
            </div>

            <div class="mb-3">
                <label for="customer" class="form-label">Customer Name</label>
               <input type="text" name="customerName" class="form-control" id="customer" placeholder="Customer Name">
            </div>

            <div class="mb-3">
                <label for="streetAddress" class="form-label">Street Address</label>
               <input type="text" name="address" class="form-control" id="streetAddress" placeholder="Street Address">
            </div>

            <div class="mb-3">
                <label for="cityStateZip" class="form-label">City State Zip</label>
                <input type="text" name="cityStateZip" class="form-control" id="cityStateZip" placeholder="City, State, Zip">
            </div>

            <div class="mb-3">
                <label for="cityStateZip" class="form-label">Monthly Charges</label>
               <input type="number" min="0" name="monthlyCharges" class="form-control" id="monthlyCharges" placeholder="340">
            </div>
            <div class="mb-3">
            <label for="additionalAddresses" class="form-label">Additional Addresses (Optional)</label>
            <textarea class="form-control" id="additionalAddresses" name="additionalAddresses" rows="4" placeholder="Enter additional addresses, one per line"></textarea>
            </div>
            <button class="btn btn-sm btn-success" type="button" onclick="generateUrl()">Generate URL</button>
        </form>
        <div id="generatedUrl"></div>
    </div>

    <script>
        function generateUrl() {
            const form = document.getElementById('form');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const contract_provider = formData.get('contract_provider') || "";
            const customerName=formData.get('customerName')||"";
            const escName=formData.get('escName')||"";
            const address=formData.get('address')||"";
            const proposalDate=formData.get('proposalDate')||"";
            const citySateZip=formData.get('citySateZip')||"";
            const monthlyCharges=formData.get('monthlyCharges')||"";
            const additionalAddresses = formData.get('additionalAddresses') || "";
            const url = `pregenerate_pdf.php?${params.toString()}`;
            document.getElementById('generatedUrl').innerHTML = `<p>Generated URL: <a href="${url}" target="_blank">${url}</a></p>`;
        }

    </script>
    
</body>
</html>