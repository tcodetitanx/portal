<?php

require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Axiom Corp');
$pdf->SetTitle('Contractual Agreement');
$pdf->SetSubject('Contractual Agreement');
$pdf->SetKeywords('Contractual Agreement');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Get data from GET parameters
$contract_provider = $_GET['contract_provider'] ?? "";
$signature = $_GET['signature'] ?? "";
$signatureDate = $_GET['signatureDate'] ?? "";
$customerName = $_GET['customerName'] ?? "";
$proposalDate = $_GET['proposalDate'] ?? "";
$escName = $_GET['escName'] ?? "";
$address = $_GET['address'] ?? "";
$cityStateZip = $_GET['cityStateZip'] ?? "";
$monthlyCharges = $_GET['monthlyCharges'] ?? 0;
$additionalAddresses = $_GET['additionalAddresses'] ?? "";
$contract_name = "axiom_". $customerName;

if (!empty($additionalAddresses)) {
    $exhibit_clause = "<p>$contract_provider also accepts to clean the properties listed by address in Exhibit A at the end of this contract. They shall be cleaned according to the same schedule and to the same extent as outlined in the contract. 
        </p>";
}
else
{
    $exhibit_clause = "";
}


// Create the content
$content = <<<EOD
        
        <img src="assets/images/logo.jpg" alt="logo">
         <p> </p>
        <h3>$contract_provider</h3>
        <p style="font-size:14px">The Undersigned ($customerName) hereby accepts the proposal $contract_provider, and the parties agree that $contract_provider's franchises and/or subcontractors will supply ($contract_provider System Services for CUSTOMER'S headquarters located at:</p>
        <ul>
            <li>Customer: $customerName</li>
            <li>Street Address: $address</li>
            <li>City State Zip: $cityStateZip</li>
        </ul>
        $exhibit_clause
        <p>Upon the following terms</p>
            <ol style="font-size:14px">
                <li> Monthly Services Charge: 
                    <div>$$monthlyCharges per month, plus taxes, if applicable; to include 5 times(s) per week services. Initial</div>
                    <div>Service Days <br> 
                        <input type="checkbox" id="monday" name="monday" value="monday" checked>
                        <label style="font-size:11px" for="monday"> Monday </label>

                        <input type="checkbox" id="tuesday" name="tuesday" value="tuesday" checked>
                        <label style="font-size:11px" for="tuesday"> Tuesday </label>

                        <input type="checkbox" id="wednesday" name="wednesday" value="wednesday" checked>
                        <label style="font-size:11px" for="wednesday"> Wednesday </label>

                        <input type="checkbox" id="thursday" name="thursday" value="thursday" checked>
                        <label style="font-size:11px" for="thursday"> Thursday </label>

                        <input type="checkbox" id="friday" name="friday" value="friday" checked>
                        <label style="font-size:11px" for="friday"> Friday </label>

                        <input type="checkbox" id="saturday" name="saturday" value="saturday">
                        <label style="font-size:11px" for="saturday"> Saturday </label>

                        <input type="checkbox" id="sunday" name="sunday" value="sunday">
                        <label style="font-size:11px" for="sunday"> Sunday </label>
                    </div>
                    <div>$contract_provider System Services are to be performed in the evening, unless otherwise agreed to by the parties.</div>
               </li> 
               <li>CUSTOMER acknowledges that ($contract_provider) will delegate all ($contract_provider) System Services to be performed hereunder to a $contract_provider franchisee and/or subcontractor and ($contract_provider) may assign this Service Agreement in its entirety to a ($contract_provider) franchisee and/or subcontractor.</li>
               <br>
               <li>Included in the Service Charge will be service, cleaning supplies, and any equipment which will be furnished by the ($contract_provider) franchisee.
                 The Service Charge does not include liners, paper supplies, and toiletries, which can be provided at CUSTOMER's expense, at competitive prices. The Service Charge also does not include any use tax, tax on sales, services or supplies, or other such tax, which taxes shall be paid by CUSTOMER. CUSTOMER agrees to reimburse ($contract_provider) the amount of any such taxes if paid by ($contract_provider) on CUSTOMER's behalf.
               </li>
               <br>
              <li>All $contract_provider System Services specified in the "($contract_provider) Service Plan" attached to this Service Agreement as Exhibit A will be provided to CUSTOMER in a satisfactory manner. CUSTOMER acknowledges that only those Services and/or Additional Services specifically identified in the ($contract_provider) Service Plan will be provided under this Service Agreement.</li>
               <br> <br>
              <li>All $contract_provider franchises have successfully completed ($contract_provider)'s comprehensive training program and are required to carry insurance and a janitorial bond.</li>
              <br>
             <li>Additional services, not included in ($contract_provider)'s Service Charge, to be performed upon request, priced per occurrence, at CUSTOMER'S expense, include:</li>
             <br>
             <br>
               
            <table border="1" cellspacing="0" cellpadding="4">
                <thead>
                    <tr style="border: 1px solid black;">
                        <th>Additional Services</th>
                        <th>Charge</th>
                        <th>Area</th>
                        <th>Square Footage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>a.</td>
                        <td>$</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>b.</td>
                        <td>$</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>c.</td>
                        <td>$</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>d.</td>
                        <td>$</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>e.</td>
                        <td>$</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

                <p>Additional Services Accepted By: -------------------------- Signature-------------------------------</p> 
                    
                <li>(a) The term of this Service Agreement is for three (3) years. This one year period shall begin on the date services are scheduled to begin.
                        This Service Agreement shall automatically extend for additional one (1) year periods, unless at least thirty (30) days prior to each anniversary of the date services are scheduled to begin, either party gives the other written notice of its intent not to renew.
                       <br> <br>
                        (b) Termination/Notice: If a party to this Service Agreement fails to perform its obligations (the "non-performing party"), the party claiming non-performance shall send the non-performing party written notice, specifying the manner of non-performance. This notice will provide that the non-performing party shall have fifteen (15) days from receipt of the notice to cure or correct the items of non-performance (the "Cure Period"). If these items are not corrected or cured within the Cure Period, the claiming party may issue a thirty (30) day written notice, of termination and/or pursue other available remedies for default.
                        <br> <br>
                        If the CUSTOMER's notice under this 117(b) concerns service issues, the CUSTOMER shall permit the $contract_provider or subcontractor access to the premises during the Cure Period to cure the service issue; and shall also accompany a $contract_provider representative on an inspection of the premises during the fifteen (15) day cure period. Failure to comply will entitle $contract_provider to collect the full amount due through the Term of this Service Agreement.
                        <br> <br>
                        (c) Notwithstanding the above, ($contract_provider) may, but shall not be obligated to, terminate this Service Agreement immediately for non-payment by CUSTOMER of Service Charges due.
                </li> <br>  <br> 
 
                <li>The Service Charge will remain in effect for one year unless there are changes in the original specifications for the premises. In the event of such changes, CUSTOMER will advise $contract_provider accordingly, and an adjustment in the Service Charge, as agreed to by the parties, will be made.</li> <br> 
                <li>CUSTOMER agrees that it will not employ or contract with any $contract_provider employee, franchisee, or any of the franchisee's employees during the term of this Service Agreement or for one hundred and eighty (180) days after termination of this Service Agreement, without $contract_provider's written consent.</li> <br> 
                <li>$contract_provider will bill CUSTOMER monthly, and CUSTOMER agrees to pay $contract_provider the amount that is due and owing under the terms of this Service Agreement within 10 days of billing date. Late payments will incur service and finance charges. In the event of default on payment, CUSTOMER agrees to pay $contract_provider's attorney's fees and costs for collection.</li> <br> 
                <li>Services shall be performed as stated in the $contract_provider Service Plan attached to this Service Agreement with the exception of the following six (6) legal holidays: New Year's Day, Memorial Day, Independence Day, Labor Day, Thanksgiving Day and Christmas Day. No Service Charge credits will be issued for these holidays. However, service can be provided on these holidays at an additional cost if required. Services shall be scheduled during the hours approved or directed by manager/owner.</li> <br> 
                <li>If "Additional Special Services" are included in the $contract_provider Service Plan attached to this Service Agreement, and if CUSTOMER cancels any periodic Special Services described therein for which a prorated monthly charge is included in CUSTOMER'S total monthly Service Charge, any amount owing by CUSTOMER for Special Services performed prior to the cancellation shall be payable in full no later than five (5) days after the cancellation.</li> <br> 
                <li>The undersigned warrant and represent that they have full authority to enter into this Service Agreement, and that it will be binding upon the parties and their respective successors and assigns. Specifically, CUSTOMER acknowledges that this Service Agreement may be assigned in its entirety to a $contract_provider, a subcontractor or another third party.</li> <br> 
                <li>This Service Agreement and attached exhibits constitute the complete agreement of the parties concerning the provision of cleaning services to the CUSTOMER, and supersedes all other prior or contemporaneous agreements between the parties, whether written or oral, on the same subject. No waiver or modification of this Service Agreement shall be valid unless in writing and executed by $contract_provider and CUSTOMER. Additionally, in no event shall the terms and conditions of any purchase order or other form subsequently submitted by CUSTOMER to $contract_provider become a part of this Service Agreement, and $contract_provider shall not be bound by any such terms and conditions.</li> <br> 
        </ol>
        <table width="100%">
            <tr>
                <td width="45%">
                    <h2>Customer</h2>
                    <br> <span></span><hr>
                    <span>Signature and Date</span>
                    <br> <hr>
                    <span style="font-size:12px">Print Name and Title, its Authorized Representative</span>
                    <br> <hr>
                    <span>Email Address</span>
                </td>
                <td width="2%"></td>
                <td width="45%">
                    <h2>$contract_provider</h2> <br>
                     <br> <hr>
                    <span>Sales Consultant (Signature and Date)</span>
                     <br> $escName <hr>
                    <span style="font-size:12px">Print Name and Title, its Authorized Representative</span>
                     <br> <hr>
                    <span>Service Start Date</span>
                </td>
            </tr>
        </table>

        <p>Please email or fax signed contract to:</p>
        <p></p>
     
            
        <img src="assets/images/logo.jpg" alt="logo">
        <p></p>
        <h4 class="heading fw-bold">$contract_provider Safety and Security Document</h4>
        <p style="font-size:14px">The $contract_provider System places great emphasis on safety and security. $contract_provider Business Owners are trained and certified on Personal Safety, Customer Account Security, Emergency Actions, Accident Investigation and Reporting, and other aspects of cleaning your facility in a safe and secure manner.</p>
        <p style="font-size:14px">To help us support your safety and security measures, please answer the questions below.</p>
        <span>Customer Name: $customerName</span> <span><hr></span> <br>
        <span>Customer Address: $address</span> </span> <span><hr></span>
        <ol style="font-size:14px">
            <li>Is protective equipment required in any parts of your facility where the $contract_provider Business Owner or its employees will be providing services? This might include hard hats, eye protection, steel-toed shoes, gloves, gowns, masks, or other personal protective gear.
                <div>
                    <input type="checkbox" id="yes1" name="yes1" value="yes1">
                    <label for="yes1">Yes</label> <br>
                    <input type="checkbox" id="no1" name="no1" value="no1">
                    <label for="no1">No</label>
                    <p>If yes, please document the equipment that is required and the areas in which it should be used.</p>
                </div>
            </li>
            <li>Will the $contract_provider Business Owner need to disarm and arm a building security system?
                <div>
                    <input type="checkbox" id="yes2" name="yes2" value="yes2">
                    <label for="yes2">Yes</label> <br>
                    <input type="checkbox" id="no2" name="no2" value="no2">
                    <label for="no2">No</label>
                </div>
            </li>

            <li>Will the $contract_provider Business Owner be given a set of keys for your facility?
                <div>
                    <input type="checkbox" id="yes3" name="yes3" value="yes3">
                    <label for="yes3">Yes</label> <br>
                    <input type="checkbox" id="no3" name="no3" value="no3">
                    <label for="no3">No</label>
                </div>
            </li>

            <li>Asbestos
                 <div>
                    <input type="checkbox" id="yes4" name="yes4" value="yes4">
                    <label for="yes4">I am aware of Asbestos in the facility where the $contract_provider Franchised Business will be providing services, and if applicable, I will provide the Asbestos Control Plan to the representative of the $contract_provider Business.</label> <br>
                    <input type="checkbox" id="no4" name="no4" value="no4">
                    <label for="no4">I am not aware of Asbestos in the facility where the $contract_provider Business will be providing services.</label>
                </div>
            </li>
            <li>Other: 
                <span><hr><br></span>
                <span><hr><br></span>
                <span><hr><br></span>
            </li>
        </ol>
          
        
          <img align="right" src="assets/images/logo.jpg" alt="logo">
           <p></p> <p></p> <p>
            <h2 align="center">Customized Service Plan and Proposal</h2>
            <p align="center" style="font-size:36px; font-weight:bold">Offices of</p>
            <p align="center" style="font-size:34px;">$customerName</p>
            <p></p> <p></p> <p> <p></p> <p>
            <p align="center">By:</p>
            <p align="center">$escName <br> Executive Sales Consultant <br>
                $contract_provider  
            </p>
            <p align="center">Date:</p>
            <p align="center">$proposalDate</p>
            <p></p>

      
          <img align="right" src="assets/images/logo.jpg" alt="logo">
          <p>Dear $customerName,</p>
          <p style="font-size:14px">Thank you for the opportunity to present this proposal, which we have customized to your needs and requests.</p>
          <p style="font-size:14px">The $contract_provider Health-Based Cleaning System Program is the first choice for offices, schools, daycares, retail businesses, restaurants, gyms, outpatient and ambulatory surgery centers, and Fortune 500 companies across the country. With the $contract_provider® Program your facility will look clean and smell clean and actually be a cleaner, healthier place for everyone.</p>
          <p style="font-size:14px">Your $contract_provider Service Plan and Service Agreement are attached. Please review them to learn exactly how the $contract_provider Program will meet and exceed your expectations.</p>
          <p style="font-size:14px">Thank you again. We look forward to working with you!</p>
          
          <table width="100%">
            <tr>
                <td width="20%">
                   <p>Sincerely, <br>$escName <br>$contract_provider</p>
                </td>
                <td width="79%">
                    
                </td>
            </tr>
        </table> 
        <p></p> <p></p>


        <table width="100%">
            <tr>
                <td width="45%">
                    <h3>Your top priorities for cleaning</h3>
                    In our conversations, you told me that the following are your biggest areas of concern regarding the cleaning of your facility: <br>
                    <div>
                        <input type="checkbox" id="office" name="office" value="office">
                        <label for="office">Offices</label> <br>
                    
                        <input type="checkbox" id="hallway" name="hallway" value="hallway">
                        <label for="hallway">Hallways</label> <br>
                   
                        <input type="checkbox" id="Kitchen" name="Kitchen" value="Kitchen">
                        <label for="Kitchen">Kitchen</label>
                        <p>$contract_provider Health-Based Cleaning System uses scientifically proven cleaning supplies, tools and techniques to ensure that these important priorities will be handled properly.</p>
                        <h3>The $contract_provider Difference</h3>
                    </div>
                </td>
                <td width="54%">
                    <img src="assets/images/remove germ.jpg" alt="clean">
                </td>
            </tr>
        </table> 
     
        <div style="font-size:14px">
            <input type="checkbox" id="office" name="office" value="office">
            <label for="office">Get the best value for your cleaning budget</label>
           <div>A lot has changed in the way that cleaning is done today. The work can be much faster than in the past. $contract_provider leads the industry in finding and using the best tools, techniques and training to give you a cleaning schedule that delivers more value within your budget.</div>
            <br>
            <input type="checkbox" id="office" name="office" value="office">
            <label for="office">Cleaner work and reception areas, better air quality.</label> 
            <div>$contract_provider Owners use multi-filtration vacuums to improve indoor air quality by removing 99.97% of dust, dirt, bacteria, mold, yeast, and particles down to 0.3 microns. In contrast, traditional commercial vacuums return 40% of the dirt they pick up directly into the air.</div>
            <br>
            <input type="checkbox" id="restroom" name="restroom" value="restroom">
            <label for="restroom">Restrooms that look, smell, and are actually clean.</label>
            <div>$contract_provider uses environmentally safe, hospital-grade disinfectant cleaning products, which are recommended by the Centers for Disease Control (CDC) and many medical studies to limit the spread of germs, especially in bathrooms.</div>
            <br> 
            <input type="checkbox" id="communication" name="communication" value="communication">
            <label for="communication">Consistent cleaning and good communication with the cleaners</label>
            <div>Your $contract_provider Owner was trained and certified to use $contract_provider Health-Based Cleaning System so that you get consistent, high-quality results. The cleaning team will use a log book to communicate notes or questions to you, and you will have direct access to them, to your local $contract_provider office, and to phone support 24 hours a day.</div>
            <br>
            <input type="checkbox" id="healthier" name="healthier" value="healthier">
            <label for="healthier">A healthier workplace with cross-contamination prevention</label>
            <div>The $contract_provider Color-Coding for Health® Program uses color-coded microfiber cleaning cloths and mop pads to prevent cross-contamination. In contrast, traditional cleaners use dirty rags and smelly string mops that merely transfer dirt and bacteria from one area to the next.</div>
        </div> 
        <p></p> <p></p> <p></p> <p></p> <p></p> <p></p> <p></p> <p></p>
        <div>
            <img class="img-responsive" src="assets/images/logo.jpg" alt="logo">
            <h2>Areas to be cleaned: 5X a week</h2>
            <table width="100%">
                <tr>
                    <td width="49%">
                        <input type="checkbox" id="entrance" name="entrance" value="entrance">
                        <label for="entrance">Entrance</label> <br>
            
                        <input type="checkbox" id="foyers" name="foyers" value="foyers">
                        <label for="foyers">Foyers</label> <br>
            
                        <input type="checkbox" id="waitingarea" name="waitingarea" value="waitingarea">
                        <label for="waitingarea">Waiting Area</label> <br>
            
                        <input type="checkbox" id="lobbyarea" name="lobbyarea" value="lobbyarea">
                        <label for="lobbyarea">Lobby Area</label> <br>
            
                        <input type="checkbox" id="generaloffice" name="generaloffice" value="generaloffice">
                        <label for="generaloffice">General Office</label> <br>
                        
                        <input type="checkbox" id="privateOffice" name="privateOffice" value="privateOffice">
                        <label for="privateOffice">Private Office</label> <br>

                        <input type="checkbox" id="executiveOffice" name="executiveOffice" value="executiveOffice">
                        <label for="executiveOffice">Executive Office</label> <br>

                        <input type="checkbox" id="conferenceRoom" name="conferenceRoom" value="conferenceRoom">
                        <label for="conferenceRoom">Conference Room</label> <br>

                        <input type="checkbox" id="fileroom" name="fileroom" value="fileroom">
                        <label for="fileroom">File Room/Area</label> <br>

                        <input type="checkbox" id="computerroom" name="computerroom" value="computerroom">
                        <label for="computerroom">Computer Rooms</label> <br>

                        <input type="checkbox" id="restroom" name="restroom" value="restroom">
                        <label for="restroom">Restrooms</label> <br>

                        <input type="checkbox" id="lunchroom" name="lunchroom" value="lunchroom">
                        <label for="lunchroom">Lunch Room/Kitchen</label> <br>
                    </td>
                    <td width="49%">
                        <input type="checkbox" id="kitchenette" name="kitchenette" value="kitchenette">
                        <label for="kitchenette">Kitchenette/Coffee Area</label> <br>

                        <input type="checkbox" id="lockers" name="lockers" value="lockers">
                        <label for="lockers">Lockers</label> <br>

                        <input type="checkbox" id="hallways" name="hallways" value="hallways">
                        <label for="hallways">Hallways</label> <br>

                        <input type="checkbox" id="landings" name="landings" value="landings">
                        <label for="landings">Landings</label> <br>

                        <input type="checkbox" id="stairways" name="stairways" value="stairways">
                        <label for="stairways">Stairways</label> <br>

                        <input type="checkbox" id="elevatorcabs" name="elevatorcabs" value="elevatorcabs">
                        <label for="elevatorcabs">Elevator Cabs</label> <br>

                        <input type="checkbox" id="lounges" name="lounges" value="lounges">
                        <label for="lounges">Lounges</label> <br>

                        <input type="checkbox" id="windows" name="windows" value="windows">
                        <label for="windows">Windows</label> <br>

                        <input type="checkbox" id="showroom" name="showroom" value="showroom">
                        <label for="showroom">Showroom Retail Areas</label> <br>

                        <input type="checkbox" id="supplyarea" name="supplyarea" value="supplyarea">
                        <label for="supplyarea">Supply/Storage Areas</label> <br>

                        <input type="checkbox" id="officeplant" name="officeplant" value="officeplant">
                        <label for="officeplant">Office Plant/Shop/Warehouse</label> <br>
                    </td>
                </tr>
            </table> 
        </div>
            


        <div>
            <h2>Other areas not listed above</h2>
            <table width="100%">
                <tr>
                    <td width="45%">
                        <h2></h2> <br>
                        <hr>
                        <span></span>
                        <br> <hr>
                    </td>
                    <td width="2%"></td>
                    <td width="45%">
                        <h2></h2> <br>
                        <br> <hr>
                        <span></span>
                        <br> <hr>
                    </td>
                </tr>
                <tr>
                    <td width="45%"><br>Exclude<br></td> 
                    <td width="2%"></td>
                    <td width="45%"></td> 
                </tr>
                <tr>
                    <td width="45%">
                        <span></span>
                        <br> <hr>
                        <span></span>
                        <br> <hr>
                       
                    </td>
                    <td width="2%"></td>
                    <td width="45%">
                        <span> </span>
                        <br> <hr>
                        <span></span>
                        <br> <hr>
                    </td>
                </tr>
            </table>
            <div>Service Days <br> <br> 
                <input type="checkbox" id="monday" name="monday" value="monday">
                <label style="font-size:11px" for="monday">Monday</label>

                <input type="checkbox" id="tuesday" name="tuesday" value="tuesday">
                <label style="font-size:11px" for="tuesday">Tuesday</label>

                <input type="checkbox" id="wednesday" name="wednesday" value="wednesday">
                <label style="font-size:11px" for="wednesday">Wednesday</label>

                <input type="checkbox" id="thursday" name="thursday" value="thursday">
                <label style="font-size:11px" for="thursday">Thursday</label>

                <input type="checkbox" id="friday" name="friday" value="friday">
                <label style="font-size:11px" for="friday">Friday</label>

                <input type="checkbox" id="saturday" name="saturday" value="saturday">
                <label style="font-size:11px" for="saturday">Saturday</label>

                <input type="checkbox" id="sunday" name="sunday" value="sunday">
                <label style="font-size:11px" for="sunday">Sunday</label>
            </div>
  
        </div>
        <br> 

        <div style="font-size:13px;">
            <a style="font-size:20px; font-weight:bold; color:black;">$contract_provider services, and how often they will be done at your facility:</a>
             <br> 
            <table width="100%">
                <tr>
                    <td width="30%"><img class="img-responsive" src="assets/images/dusting.jpg" alt="dusting"></td> 
                    <td width="68%"><p></p> <h3>DUSTING AND DISINFECTION</h3></td> 
                </tr>
            </table>

            <table border="1" width="100%" cellpadding="2">
                <thead>
                    <tr>
                        <th width="20%">Service Task</th>
                        <th width="60%">Description</th>
                        <th width="20%">Frequency</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%">Counters, Sinks</td>
                        <td width="60%">Clean and disinfect counters and sinks</td>
                        <td width="20%">1 time per week</td>
                    </tr>
                    <tr>
                        <td>Detail Dust and Clean</td>
                        <td>Thoroughly dust and clean accessible fixtures and office furniture including file cabinets, desks, credenzas, counter tops, display units, window sills.</td>
                        <td>1 time per week</td>
                    </tr>
                    <tr>
                        <td>Dust Blinds, Jams, Lights</td>
                        <td>Dust blinds, jams, light fixtures and ceiling vents accessible from the floor</td>
                        <td>1 time per month</td>
                    </tr>
                    <tr>
                        <td>High and Low Dusting</td>
                        <td>Dust high and low vertical and horizontal surfaces and corners not cleaned in the course of normal dusting not to exceed 12 feet.</td>
                        <td>1 time per month</td>
                    </tr>
                    <tr>
                        <td>High Touch Points</td>
                        <td>Clean and disinfect high touch points such as light switches and door knobs.</td>
                        <td>3 times per week</td>
                    </tr>
                    <tr>
                        <td>Spot Clean Internal Glass</td>
                        <td>Spot clean internal partition glass to remove smudges and fingerprints.</td>
                        <td>2 times per week</td>
                    </tr>
                    <tr>
                        <td>Spot Dust and Clean</td>
                        <td> Spot dust and clean visible soils on fixtures and office furniture including file cabinets, desks, credenzas, counter tops, display units and window sills. </td>
                        <td> 2 times per week </td>
                    </tr>
                    <tr>
                        <td> Thoroughly Clean Internal Glass </td>
                        <td> Thoroughly clean and disinfect internal partition glass. </td>
                        <td> 1 times per week</td>
                    </tr>
                    <tr>
                        <td> Vacum Furnishings or Wet Swip </td>
                        <td> Vacum fabric-covered ivrnishings and or wet wipe other furniture to remove visible dust or soil </td>
                        <td> 1 times per month </td>
                    </tr>
                </tbody>
            </table>
            <p> </p>
            <table width="100%">
                <tr>
                    <td width="30%"> <img class="img-responsive" src="assets/images/vacum.jpg" alt=vacum""> </td> 
                    <td width="68%"> <p> </p> <h3> CARPET AND FLOOR CARE </h3>  </td> 
                </tr>
            </table>

            <table border="1" cellpadding="2">
                <thead>
                    <tr>
                        <th width="20%">Service Task </th>
                        <th width="60%">Description </th>
                        <th width="20%">Frequency </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%"> Damp Vlop Hard Surface </td>
                        <td width="60%"> Damp mop hard surface floors using a no-dip protocol and changing. pad often to ensure removal of dirt. </td>
                        <td width="20%">  1 times per week </td>
                    </tr>
                    <tr>
                        <td> Dust mop hard service floor </td>
                        <td> Dry mop hard surface floors using a dust mop, vacuum or dry/wet mop </td>
                        <td> 2 times per week </td>
                    </tr>
                    <tr>
                        <td> Spot Vacuum High Traffic Areas </td>
                        <td> Spot vacuum high-traffic areas on days when wall-to-wall vacuuming is not needed. </td>
                        <td> 2 times per month </td>
                    </tr>
                    <tr>
                        <td>Wall-to-Wall Vacuum </td>
                        <td> Detail vacum accessible carpeted areas with approved HEPA backpack units.</td>
                        <td> 1 times per month </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <div style="font-size:13px">
            <table width="100%">
                <tr>
                    <td width="30%"> <img class="img-responsive" src="assets/images/restroom.jpg" alt="restroom">  </td> 
                    <td width="68%"> <p> </p> <h3> RESTROOM SERVICES </h3> </td> 
                </tr>
            </table>
                
            <table width="100%" border="1" cellpadding="2">
                <thead>
                    <tr>
                        <th  width="20%"> Service Task </th>
                        <th  width="60%"> Description </th>
                        <th  width="20%"> Frequency </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%"> Clean and Disinfecl Restrooms </td>
                        <td width="60%"> 
                            <ul>
                            <li>Restroom Fixtures: Clean and polish dispensers and fixtures. Clean and disinfect wash basins, toilet bowls, urinals and counter tops. </li>
                            <li>Restroom Walls: Clean accessible walls and toilet partitions to remove visible soil.  </li>
                            <li>Restroom Floors: : Mop all floors using coded microfiber flat mopping system and disinfecting finished floor cleaner. </li>
                            <li>Restroom Mirrors: Polish all chrome and mirrors. </li>
                            <li>Restroom Supplies: : Restock expendable products such as paper towels, toilet tissue, hand soap, liners and deodorant products from customer inventory.</li>
                            <li>Restroom Trash Removal: Empty trash cans, replace liners, spot clean receptacles as needed and take trash to designated area. </li>
                            </ul>
                        </td>
                        <td width="20%"> Daily </td>
                    </tr>
                </tbody>
            </table>
             <p> </p>
            <table width="100%">
                <tr>
                    <td width="30%"> <img class="img-responsive" src="assets/images/trash.jpg" alt="trash"> </td> 
                    <td width="68%"> <p> </p> <h3> TRASH AND MISCELLANEOUS </h3> </td> 
                </tr>
            </table>

            <table border="1" cellpading="2">
                <thead>
                    <tr>
                        <th width="20%">Service Task </th>
                        <th width="63%">Description </th>
                        <th width="17%">Frequency </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%"> Clean and Disinfect Telephones </td>
                        <td width="63%"> Clean and sanitize Telephones </td>
                        <td width="17%"> 1 times per week </td>
                    </tr>
                    <tr>
                        <td> Empty Cans and Remove Trash  </td>
                        <td> Empty trash that is contained in trash cans, in an area designated specifically for trash, or clearly labeled as trash and transport to customer's trash removal or storage area. Replace liners, spot clean receptacles as needed and take trash to designated area on customer premises. Please note: Any item that is in trash cans, designated trash areas, or clearly labeled as trash will be considered trash regardless of the content, and its loss will not be the responsibility of the   $contract_provider  Business Owner or   $contract_provider </td>
                        <td> 3 times per week </td>
                    </tr>
                    <tr>
                        <td> Sanitize Drinking Fountains/Water Coolers </td>
                        <td> Clean and sanitize drinking fountains and water coolers. </td>
                        <td> 3 times per week </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <div style="font-size:13px">
            <table width="100%">
                <tr>
                    <td width="30%"> <img class="img-responsive" src="assets/images/kitchen.jpg" alt="kitchen"> </td> 
                    <td width="68%"> <p> </p>  <h3> KITCHEN AREAS </h3> </td> 
                </tr>
            </table>
               
            <table border="1" cellpading="2">
                <thead>
                    <tr>
                        <th width="20%"> Service Task </th>
                        <th width="62%"> Description </th>
                        <th width="18%"> Frequency </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%"> clean Microwave(s) </td>
                        <td width="62%"> Thoroughly clean inside and outside of microwave with all-purpose disinfectant cleaner to rinse food contact surfaces. </td>
                        <td width="18%"> 1 times per week </td>
                    </tr>
                    <tr>
                        <td> Kitchen Counters, Tables and Sinks </td>
                        <td> Clean and disinfect kitchen counters, tables and sinks. </td>
                        <td> 3 times per week </td>
                    </tr>
                    <tr>
                        <td> Spot Clean Refrigerator  Exterior </td>
                        <td> Use all-purpose disinfectant cleaner to wipe smudges and fingerprints from the outside (exterior) of the refrigerator. </td>
                        <td> 3 times per week </td>
                    </tr>
                    <tr>
                        <td> Spot clean kitchen counters, tables and sinks to remove visible soil. </td>
                        <td> 3 times per week </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <div style="font-size:13px">
            <table width="100%">
                <tr>
                    <td width="30%"> <img src="assets/images/closingTask.jpg" alt="closingtask">  <br> </td> 
                    <td width="60%"> <p> </p>  <p> </p> <h3> Closing Tasks: </h3> </td> 
                </tr>
            </table>
           <p> </p>
            <table border="1" width="100%" cellpadding="2">
                <thead>
                    <tr>
                        <th width="80%"> CLOSING INSTRUCTIONS </th>
                        <th width="20%"> Frequency </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="80%"> Clean and organize janitor closet. </td>
                        <td width="20%"> 3 times per week </td>
                    </tr>
                    
                    <tr>
                        <td> Turn off lights (as instructed).</td>
                        <td> 3 times per week  </td>
                    </tr>
                    <tr>
                        <td> Lock doors and windows (as instructed). </td>
                        <td> 3 times per week </td>
                    </tr>
                    <tr>
                        <td> Set alarms (as instructed), </td>
                        <td> 3 times per week </td>
                    </tr>
                    <tr>
                        <td> Notify customer of any observed irregularities, burnt out lights etc. </td>
                        <td> 3 times per week </td>
                    </tr>
                </tbody>
            </table>
        </div>
        EOD;
        $pdf->writeHTML($content, true, false, true, false, '');

if (!empty($additionalAddresses)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Exhibit A: Additional Addresses', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $addresses = explode("\n", $additionalAddresses);
    foreach ($addresses as $address) {
        $pdf->MultiCell(0, 10, trim($address), 0, 'L');
    }
}

// Add footer with legal verbiage
$pdf->SetY(-15);
$pdf->SetFont('helvetica', '', 8);
ob_end_clean(); 
// Output the PDF

$pdf->Output($contract_name, 'I');