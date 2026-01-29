<style>
    .payslip-card {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .payslip-card h2, .payslip-card h4 {
      text-align: center;
      margin: 5px 0;
    }

    .payslip-section {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
      flex-wrap: wrap;
    }

    .payslip-section div {
      width: 48%;
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }

    th {
      background: #f0f0f0;
    }

    .total-row {
      font-weight: bold;
    }

    .net-pay {
      text-align: center;
      margin-top: 20px;
      font-size: 18px;
    }

    .signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
    }

    .signature-block {
      width: 40%;
      text-align: center;
    }

    .signature-line {
      border-top: 1px solid #000;
      margin-top: 40px;
    }

    .footer-note {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
      color: #555;
    }
  </style>

<div class="payslip-card">
                <h2>Payslip</h2>
                <br />
                <div style="text-align:center; padding-bottom: 40px;">
                    <img src="{{ isset($companySettings->logo_image) && !empty($companySettings->logo_image) ? asset($companySettings->logo_image) : asset('assets/img/user-icon.webp') }}"
                        width="auto" height="40" alt="">
                    <h4>{{ $companySettings->company_name }}</h4>
                    <div style="padding: 0 24%;">
                        <p style="text-align:center;">{{ $companySettings->address }}</p>
                    </div>
                </div>



                <div class="payslip-section">
                    <div>Date of Joining: <strong><span id="doj"></span></strong></div>
                    <div>Employee Name: <strong><span id="empName"></span></strong></div>
                    <div>Pay Period: <strong><span id="payPeroid"></span></strong></div>
                    <div>Designation: <strong><span id="designation"></span></strong></div>
                    <div>Worked Days: <strong><span id="workedDays"></span></strong></div>
                    <div>Department: <strong><span id="department"></span></strong></div>
                </div>
                <div >

                <table>
                    <thead>
                        <tr>
                            <th >Earnings</th>
                            <th>Amount</th>
                            <th>Deductions</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="earningsContainer">

                    </tbody>
                </table>

                <!-- <table>
                    <thead>
                        <tr>
                            
                            <th style="width: 80%;">Deductions</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="deductionsContainer">

                    </tbody>
                </table> -->
                </div>
                

                <div class="net-pay">
                    <strong>Net Pay: <span id="modalNetPay"></span></strong><br>
                    <em><span id="wordNetPay"></span></em>
                </div>

                <!-- <div class="signatures">
        <div class="signature-block">
          Employer Signature
          <div class="signature-line"></div>
        </div>
        <div class="signature-block">
          Employee Signature
          <div class="signature-line"></div>
        </div>
      </div> -->

                <p class="footer-note">This is a system generated payslip, hence no signature is required</p>
            </div>