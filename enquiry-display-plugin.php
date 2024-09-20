<?php
/*
Plugin Name: Comprehensive Styled Enquiry Display
Description: Displays detailed styled enquiry information based on URL parameter
Version: 2.0
Author: Your Name
*/

// Add rewrite rule for enquiry display
function ced_add_rewrite_rule() {
    add_rewrite_rule('^enquiry/([0-9]+)/?', 'index.php?enquiry_id=$matches[1]', 'top');
}
add_action('init', 'ced_add_rewrite_rule');

// Add query var for enquiry_id
function ced_query_vars($query_vars) {
    $query_vars[] = 'enquiry_id';
    return $query_vars;
}
add_filter('query_vars', 'ced_query_vars');

// Enqueue the CSS file
function ced_enqueue_styles() {
    $css_url = plugins_url('css/enquiry-display-plugin.css', __FILE__);
    wp_enqueue_style('ced-styles', $css_url, array(), '1.0.0');
    
    // Enqueue print-specific CSS
    $print_css_url = plugins_url('css/enquiry-display-print.css', __FILE__);
    wp_enqueue_style('ced-print-styles', $print_css_url, array(), '1.0.0', 'print');
}
add_action('wp_enqueue_scripts', 'ced_enqueue_styles');

// Display enquiry content
function ced_display_enquiry() {
    $enquiry_id = get_query_var('enquiry_id');
    if ($enquiry_id) {
        $acf_data = get_fields($enquiry_id);
        if ($acf_data) {
            echo ced_generate_styled_html($acf_data, $enquiry_id);
        } else {
            echo '<p>Enquiry not found.</p>';
        }
        exit;
    }
}
add_action('template_redirect', 'ced_display_enquiry');

function ced_number_to_words($number) {
    $ones = array(
        1 => "ONE", 2 => "TWO", 3 => "THREE", 4 => "FOUR", 5 => "FIVE", 
        6 => "SIX", 7 => "SEVEN", 8 => "EIGHT", 9 => "NINE", 10 => "TEN", 
        11 => "ELEVEN", 12 => "TWELVE", 13 => "THIRTEEN", 14 => "FOURTEEN", 
        15 => "FIFTEEN", 16 => "SIXTEEN", 17 => "SEVENTEEN", 18 => "EIGHTEEN", 
        19 => "NINETEEN"
    );
    $tens = array(
        2 => "TWENTY", 3 => "THIRTY", 4 => "FORTY", 5 => "FIFTY", 
        6 => "SIXTY", 7 => "SEVENTY", 8 => "EIGHTY", 9 => "NINETY"
    );
    $hundreds = array(
        "HUNDRED", "THOUSAND", "LAKH", "CRORE"
    );

    if ($number == 0) {
        return "ZERO";
    }

    $words = "";

    if (($number / 10000000) > 1) {
        $words .= ced_number_to_words(floor($number / 10000000)) . " CRORE ";
        $number %= 10000000;
    }

    if (($number / 100000) > 1) {
        $words .= ced_number_to_words(floor($number / 100000)) . " LAKH ";
        $number %= 100000;
    }

    if (($number / 1000) > 1) {
        $words .= ced_number_to_words(floor($number / 1000)) . " THOUSAND ";
        $number %= 1000;
    }

    if (($number / 100) > 1) {
        $words .= ced_number_to_words(floor($number / 100)) . " HUNDRED ";
        $number %= 100;
    }

    if ($number > 0) {
        if ($number < 20) {
            $words .= $ones[$number];
        } else {
            $words .= $tens[floor($number / 10)];
            if (($number % 10) > 0) {
                $words .= " " . $ones[$number % 10];
            }
        }
    }

    return trim($words);
}


// Generate styled HTML for enquiry data


function ced_generate_styled_html($data, $enquiry_id) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enquiry #<?php echo esc_html($enquiry_id); ?></title> 
        <?php wp_head(); ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
    </head>



<body class="enquiry-body">
<div id="enquiry-content">

<div class="details-card">
        <div class="details-header">Enquiry Details</div>
        <div class="details-content">
            <div class="enquiry-details">
                <div class="enquiry-id">
                    Enquiry ID<br>
                    EN140111
                </div>
                <div class="date-details">
                    <div>Pickup Date : 28-Sep-2024 10.00 AM</div>
                    <div>Return Date : 01-Oct-2024 18.00 PM</div>
                </div>
                <div class="enquiry-stamp">
                    ENQUIRY
                </div>
            </div>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Welcome</div>
        <div class="details-content">
            <p class="welcome-message">
                Hi <span class="customer-name">Mr. <?php echo esc_html($data['guest_details']['name']); ?></span>,
            </p>
            <p class="welcome-message">
                <span>Warm Greetings from Maa Santoshi Tours And Travels!</span> We're thrilled that you've chosen us for your travel needs. Thank you for your query at Maa Santoshi Tours And Travels. We've prepared a special quotation just for you.
            </p>
            <p class="welcome-message">
                Please note: This response to your query doesn't confirm the Cab / Coach / Hotel / Tour Package. To secure your booking with instant confirmation, please contact our customer service.
            </p>
        </div>
    </div>

    <div class="two-column-section">
        <div class="column">
            <div class="details-card">
                <div class="details-header">Guest Details</div>
                <div class="details-content">
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo esc_html($data['guest_details']['name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact Number:</span>
                        <span class="detail-value"><?php echo esc_html($data['guest_details']['contact_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo esc_html($data['guest_details']['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Number of Passengers:</span>
                        <span class="detail-value"><?php echo esc_html($data['guest_details']['no_of_passengers']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value"><?php echo esc_html($data['guest_details']['durations']); ?> days</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="details-card">
                <div class="details-header">Journey Details</div>
                <div class="details-content">
                    <h3>Arrival</h3>
                    <div class="detail-item">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?php echo esc_html($data['journey_details']['arrival_details']['arrival_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo esc_html($data['journey_details']['arrival_details']['arrival_address']); ?></span>
                    </div>
                    <h3>Departure</h3>
                    <div class="detail-item">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?php echo esc_html($data['journey_details']['departure_details']['arrival_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo esc_html($data['journey_details']['departure_details']['departure_address']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Itinerary</div>
        <div class="details-content">
            <div class="itinerary-container">
                <?php foreach ($data['itinery_details'] as $index => $day): ?>
                    <div class="itinerary-day">
                        <div class="day-header">Day <?php echo $index + 1; ?>: <?php echo esc_html($day['title']); ?></div>
                        <?php
                        $activities = explode("\n", $day['description']);
                        ?>
                        <?php foreach ($activities as $activity): ?>
                            <?php if (trim($activity)): ?>
                                <div class="itinerary-item">
                                    <div class="itinerary-time"><?php echo esc_html($day['date&day']); ?></div>
                                    <div class="itinerary-description"><?php echo esc_html($activity); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Rate Details</div>
        <div class="details-content">
            <table>
                <tr>
                    <th>#</th>
                    <th>TAXI NAME</th>
                    <th>QTY</th>
                    <th>UNIT RATE (IN RS.)</th>
                    <th>TOTAL RATE (IN RS.)</th>
                </tr>
                <?php 
                $subtotal = 0;
                foreach ($data['taxi_details'] as $index => $taxi): 
                    $total_rate = $taxi['qty'] * $taxi['unit_rate'];
                    $subtotal += $total_rate;
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo esc_html($taxi['taxiname']); ?></td>
                        <td><?php echo esc_html($taxi['qty']); ?></td>
                        <td><?php echo number_format($taxi['unit_rate'], 2); ?></td>
                        <td><?php echo number_format($total_rate, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" class="text-right font-bold">SUB TOTAL</td>
                    <td><?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php
                $gst_rate = $data['tax&dis']['gst'];
                $gst_amount = $subtotal * ($gst_rate / 100);
                $total_with_gst = $subtotal + $gst_amount;
                $total_rounded = ceil($total_with_gst);
                $advanced_payment = 5000.00;
                $balance_due = $total_rounded - $advanced_payment;
                ?>
                <tr>
                    <td colspan="4" class="text-right font-bold">GST (<?php echo $gst_rate; ?> %)</td>
                    <td><?php echo number_format($gst_amount, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right font-bold">TOTAL CAB PRICE(ROUND)</td>
                    <td><?php echo number_format($total_rounded, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right font-bold">TOTAL(ROUND)</td>
                    <td><?php echo number_format($total_rounded, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right font-bold">ADVANCED TO BE PAID</td>
                    <td><?php echo number_format($advanced_payment, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Tax & Discount</div>
        <div class="details-content">
            <table>
                <tr>
                    <th>GST</th>
                    <td><?php echo esc_html($data['tax&dis']['gst']); ?>%</td>
                </tr>
                <tr>
                    <th>Discount</th>
                    <td><?php echo esc_html($data['tax&dis']['discount']); ?>%</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Inclusion and Exclusion</div>
        <div class="details-content">
            <h3>What your package includes</h3>
            <ul>
                <li>All Transport related charges (Fuel, Toll Charges, State Taxes & Parking Charges)</li>
                <li>Driver Allowances</li>
                <li>Cab Will be with you from your arrival till departure</li>
                <li>All Transfer & Sightseeing as per itinerary</li>
                <li>GST (5%) added to Total Billing</li>
            </ul>
            <h3>What Your Package Doesn't Includes</h3>
            <ul>
                <li>Hotel Booking</li>
                <li>Any Airfare / Train Fare</li>
                <li>Boating Charges</li>
                <li>Travel Insurance</li>
                <li>Monument Entrance Fees, Guide, other expenses not mentioned in inclusion</li>
                <li>Any sightseeing or excursion that is not mentioned in the itinerary</li>
                <li>Any item of personal requirement, such as drinks, Tips, laundry, telephone, etc</li>
                <li>Any medical or evacuation expenses</li>
                <li>Any expenses occur due to natural climate, security, roadblocks, Vehicle Malfunctions and other unexpected reason</li>
                <li>Anything not mentioned in Inclusion</li>
            </ul>
        </div>
    </div>

    <div class="details-card">
        <div class="details-header">Payment Options</div>
        <div class="details-content">
            <div class="highlight">
                <strong>NOTE:</strong> After successful payment, share the UTR Number / Screenshot via Whatsapp on 83379-11111 or mail us on account@patratravels.com with your Enquiry ID / Confirmed ID.
            </div>
            
            <h3>Wallet Options</h3>
            <ul>
                <li><strong>Google Pay:</strong> 83379-11111</li>
                <li><strong>PhonePe:</strong> 83379-11111</li>
                <li><strong>Amazon Pay:</strong> 83379-11111</li>
                <li><strong>Paytm:</strong> 83379-11111</li>
            </ul>
            
            <h3>UPI ID</h3>
            <p>Q790352025@ybl</p>
            
            <h3>Bank Account Details (Money Transfer via IMPS or NEFT or RTGS)</h3>
            <div class="bank-details">
                <?php
                $banks = array(
                    array("ICICI Bank", "658605601391", "Patra Tours And Travels", "Vivekananda Marg, BBSR", "ICIC0006586"),
                    array("SBI Bank", "36450525199", "Patra Tours And Travels", "Rajpath Branch, BBSR", "SBIN0007188"),
                    array("Bank of India", "557120110000118", "Patra Tours And Travels", "Tankapani Rd, BBSR", "BKID0005571"),
                    array("HDFC Bank", "50200008530861", "Patra Tours And Travels", "Vivekananda Marg, BBSR", "HDFC0002402"),
                    array("Yes Bank", "9383800003577", "Patra Tours And Travels", "Bapuji Nagar, BBSR", "YESB0000093")
                );
                foreach ($banks as $bank) {
                    echo '<div class="bank-card">';
                    echo '<p><strong>Bank Name:</strong> ' . esc_html($bank[0]) . '</p>';
                    echo '<p><strong>Account Number:</strong> ' . esc_html($bank[1]) . '</p>';
                    echo '<p><strong>Account Name:</strong> ' . esc_html($bank[2]) . '</p>';
                    echo '<p><strong>Bank Address:</strong> ' . esc_html($bank[3]) . '</p>';
                    echo '<p><strong>IFSC CODE:</strong> ' . esc_html($bank[4]) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="details-card">
    <div class="details-header">Cancellation Policy</div>
    <div class="details-content">
        <ul>
            <li>Within 0 - 24 hours: <strong>15%</strong> Cancellation charges on the total billing amount</li>
            <li>Within 24 - 48 hours: <strong>10%</strong> Cancellation charges on the total billing amount</li>
            <li>Above 48 hours: <strong>5%</strong> Cancellation charges on the total billing amount</li>
        </ul>
    </div>
</div>

<div class="details-card">
    <div class="details-header">Terms and Conditions</div>
    <div class="details-content">
        <ul>
            <li>Driver Details And Cab Regn Number Details will be provided before 03 Hours of Arrival Time</li>
            <li>Up-gradation of Cab is Possible on Extra Charges</li>
            <li>Standing AC {Per 30 Min @ Rs. 400 (Sedan & SUV Cabs), Rs.600 (Tempo Traveller), Rs.1000(Coach)}</li>
            <li>Per Day Maximum Allowed Time for Transportation is 12 Hrs</li>
            <li>Maximum Driver Duty Time is up to 10 Pm</li>
            <li>While driving on Ghat roads, Air-Conditioning shall remain switched off</li>
            <li>All disputes are subject to Bhubaneswar legal jurisdiction only</li>
        </ul>
    </div>
</div>

<div class="details-card">
    <div class="details-header">Disclaimers</div>
    <div class="details-content">
        <ul>
            <li>In case of any sudden breakdown or unforeseen incident, the company will not be liable for any compensation or damages. Not with standing the above, the service provider/company will initiate all possible prompt and appropriate action to recover the situation there upon.</li>
            <li>The Company will not be responsible for any stolen luggage, compensation or damages if occurred during your journey. Nonetheless, the company will help you in every possible manner to recover from the situation.</li>
            <li>The company will not be liable for any refund or compensation due to restriction of the cab / Bus movement or denial of entry to any area by the city administration or traffic police.</li>
        </ul>
    </div>
</div>

<div class="details-card">
    <div class="details-header">Check the Authenticity of our Company</div>
    <div class="details-content">
        <ul>
            <li>Patra Tours And Travels - Approved by Ministry of Tourism, Govt. of India ( <a href="#">Site Link 1</a>, <a href="#">Site Link 2</a> )</li>
            <li>Patra Tours And Travels - Approved by Odisha Tourism, Govt. of Odisha ( <a href="#">Site Link</a> )</li>
            <li>Patra Tours And Travels - Member of "Indian Association of Tour Operators" ( IATO) ( <a href="#">Site Link</a> )</li>
        </ul>
    </div>
</div>

<div class="details-card">
    <div class="details-header">Check Authentic Feedback of our Company</div>
    <div class="details-content">
        <p><a href="#" class="button">CLICK HERE</a></p>
    </div>
</div>
        <div class="footer">
            <p>Thank you for choosing our services. For any queries, please contact us.</p>
        </div>




</div>
   





<div class="pdf-download">
            <button onclick="generatePDF()" class="button">Download PDF</button>
        </div>

        <script>
function generatePDF() {
    console.log('Generate PDF function called');
    
    const content = document.getElementById('enquiry-content');
    if (!content) {
        console.error('Element with id "enquiry-content" not found');
        alert('PDF generation failed: Content element not found');
        return;
    }

    if (typeof window.jspdf === 'undefined' || typeof html2canvas === 'undefined') {
        console.error('Required libraries not loaded');
        alert('PDF generation failed: Required libraries not loaded');
        return;
    }

    const { jsPDF } = window.jspdf;

    // Create a new jsPDF instance
    const pdf = new jsPDF('p', 'pt', 'a4');
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const margin = 40; // Margin around the page

    // Function to add a new page
    const addPage = () => {
        pdf.addPage();
        return pdf.internal.getCurrentPageInfo().pageNumber;
    };

    // Recursively add content to PDF
    const addContentToPDF = (element, currentPage = 1) => {
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            logging: true
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = pageWidth - 2 * margin;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            let heightLeft = imgHeight;
            let position = 0;

            pdf.setPage(currentPage);

            // Add image to current page
            pdf.addImage(imgData, 'PNG', margin, position + margin, imgWidth, imgHeight);
            heightLeft -= (pageHeight - 2 * margin);
            
            // Add new pages if content overflows
            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                currentPage = addPage();
                pdf.addImage(imgData, 'PNG', margin, position + margin, imgWidth, imgHeight);
                heightLeft -= (pageHeight - 2 * margin);
            }

            // Process next sibling element if exists
            if (element.nextElementSibling) {
                addContentToPDF(element.nextElementSibling, currentPage);
            } else {
                // Save PDF when all elements are processed
                pdf.save('enquiry_<?php echo esc_js($enquiry_id); ?>.pdf');
                console.log('Multi-page PDF generated and saved');
            }
        }).catch(error => {
            console.error('Error in html2canvas:', error);
            alert('PDF generation failed: ' + error.message);
        });
    };

    // Start processing with the first child of the content container
    if (content.firstElementChild) {
        addContentToPDF(content.firstElementChild);
    } else {
        console.error('No content found in the enquiry-content container');
        alert('PDF generation failed: No content found');
    }
}

window.addEventListener('load', function() {
    if (typeof window.jspdf === 'undefined') {
        console.error('jsPDF library not loaded');
    } else {
        console.log('jsPDF library loaded successfully');
    }
    
    if (typeof html2canvas === 'undefined') {
        console.error('html2canvas library not loaded');
    } else {
        console.log('html2canvas library loaded successfully');
    }

    if (!document.getElementById('enquiry-content')) {
        console.error('Element with id "enquiry-content" not found');
    } else {
        console.log('Enquiry content element found');
    }
});
</script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}