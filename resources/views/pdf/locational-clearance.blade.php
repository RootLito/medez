<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application for Locational Clearance</title>
    <style>
        @page {
            margin: 15px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title {
            font-size: 16px;
            font-weight: bold;
            font-family: 'Arial Black', sans-serif;
            margin-bottom: 10px;
        }
        .header-table, .form-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 2px 4px;
        }
        .form-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .value {
            font-size: 10px;
            margin-top: 2px;
        }
        .checkbox-group {
            margin-top: 3px;
        }
        .checkbox {
            display: inline-block;
            width: 9px;
            height: 9px;
            border: 1px solid #000;
            margin-right: 3px;
            text-align: center;
            line-height: 8px;
            font-size: 8px;
        }
        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 120px;
        }
        .notary-section {
            margin-top: 15px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="text-center title">APPLICATION FOR LOCATIONAL CLEARANCE</div>

    <!-- Header Fields -->
    <table class="header-table" style="margin-bottom: 8px;">
        <tr>
            <td width="15%" class="font-bold">Application No.</td>
            <td width="35%"><span class="underline">{{ $clearance->application_no ?? '___________________' }}</span></td>
            <td width="15%" class="font-bold">Date Issued</td>
            <td width="35%"><span class="underline">{{ optional($clearance->date_issued)->format('M d, Y') ?? '___________________' }}</span></td>
        </tr>
        <tr>
            <td class="font-bold">Date of Receipt</td>
            <td><span class="underline">{{ optional($clearance->date_of_receipt)->format('M d, Y') ?? '___________________' }}</span></td>
            <td class="font-bold">Amount Paid</td>
            <td><span class="underline">{{ $clearance->amount_paid ? 'P ' . number_format($clearance->amount_paid, 2) : '___________________' }}</span></td>
        </tr>
        <tr>
            <td class="font-bold">O.R. Number</td>
            <td><span class="underline">{{ $clearance->or_number ?? '___________________' }}</span></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Main Grid Form -->
    <table class="form-table">
        <tr>
            <td width="50%">
                <div class="label">1. NAME OF APPLICANT <span style="font-weight:normal; font-size: 8px;">(LAST, FIRST, MIDDLE)</span></div>
                <div class="value">{{ $clearance->applicant_name ?? '' }}</div>
            </td>
            <td width="50%">
                <div class="label">2. NAME OF CORPORATION</div>
                <div class="value">{{ $clearance->corporation_name ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">3. ADDRESS/TELEPHONE REPRESENTATIVE</div>
                <div class="value">{{ $clearance->rep_address_tel ?? '' }}</div>
            </td>
            <td>
                <div class="label">4. ADDRESS/TEL. OF CORPORATION</div>
                <div class="value">{{ $clearance->corp_address_tel ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">5. NAME OF AUTHORIZED REPRESENTATIVE</div>
                <div class="value">{{ $clearance->auth_rep_name ?? '' }}</div>
            </td>
            <td>
                <div class="label">6. ADDRESS/TEL. OF AUTHORIZED REPRESENTATIVE</div>
                <div class="value">{{ $clearance->auth_rep_address_tel ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">7. PROJECT TYPE</div>
                <div class="value">{{ $clearance->project_type ?? '' }}</div>
            </td>
            <td>
                <div class="label">8. PROJECT NATURE</div>
                <div class="value">{{ $clearance->project_nature ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">9. PROJECT LOCATION</div>
                <div class="value">{{ $clearance->project_location ?? '' }}</div>
            </td>
            <td>
                <div class="label">10. PROJECT AREA <span style="font-weight:normal;">(in square meters)</span></div>
                <div class="value">
                    Lot Area: <span class="underline" style="min-width: 60px;">{{ $clearance->lot_area_sqm ?? '' }}</span> Sq.m. 
                    Perimeter: <span class="underline" style="min-width: 50px;">{{ $clearance->perimeter_m ?? '' }}</span> m.<br>
                    Building Area: <span class="underline" style="min-width: 60px;">{{ $clearance->building_area_sqm ?? '' }}</span> Sq.m.
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">11. RIGHT OVER LAND</div>
                <div class="checkbox-group">
                    <span class="checkbox">{{ $clearance->right_over_land == 'owner' ? '✓' : '' }}</span> OWNER
                    <span class="checkbox" style="margin-left: 10px;">{{ $clearance->right_over_land == 'heirs' ? '✓' : '' }}</span> HEIRS<br>
                    <span class="checkbox">{{ $clearance->right_over_land == 'lease' ? '✓' : '' }}</span> LEASE
                    <span class="checkbox" style="margin-left: 17px;">{{ $clearance->right_over_land == 'others' ? '✓' : '' }}</span> OTHERS (SPECIFY) <span class="underline" style="min-width: 80px;">{{ $clearance->right_over_land_others }}</span>
                </div>
            </td>
            <td>
                <div class="label">12. PROJECT TENURE</div>
                <div class="checkbox-group">
                    <span class="checkbox">{{ $clearance->tenure == 'permanent' ? '✓' : '' }}</span> PERMANENT<br>
                    <span class="checkbox">{{ $clearance->tenure == 'temporary' ? '✓' : '' }}</span> TEMPORARY
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">13. EXISTING LAND USE OF THE PROJECT SITE</div>
                <table style="width: 100%; border: none; margin-top: 3px;">
                    <tr style="border: none;">
                        <td style="border: none; padding: 0;" width="25%">
                            <span class="checkbox">{{ $clearance->land_use == 'residential' ? '✓' : '' }}</span> RESIDENTIAL<br>
                            <span class="checkbox">{{ $clearance->land_use == 'institutional' ? '✓' : '' }}</span> INSTITUTIONAL
                        </td>
                        <td style="border: none; padding: 0;" width="25%">
                            <span class="checkbox">{{ $clearance->land_use == 'commercial' ? '✓' : '' }}</span> COMMERCIAL<br>
                            <span class="checkbox">{{ $clearance->land_use == 'industrial' ? '✓' : '' }}</span> INDUSTRIAL
                        </td>
                        <td style="border: none; padding: 0;" width="25%">
                            <span class="checkbox">{{ $clearance->land_use == 'others' ? '✓' : '' }}</span> OTHERS<br>
                            <span class="checkbox">{{ $clearance->is_tenanted ? '✓' : '' }}</span> TENANTED
                        </td>
                        <td style="border: none; padding: 0;" width="25%">
                            <span class="checkbox">{{ $clearance->land_use == 'vacant' ? '✓' : '' }}</span> VACANT/IDLE<br>
                            <span class="checkbox">{{ !$clearance->is_tenanted ? '✓' : '' }}</span> NOT TENANTED
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 4px;">
                    <span class="checkbox">{{ $clearance->land_use == 'agriculture' ? '✓' : '' }}</span> AGRICULTURE (Specify Crops) <span class="underline" style="min-width: 250px;">{{ $clearance->agriculture_crops }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">14. PROJECT COST <span style="font-weight:normal;">(in pesos write in figures)</span></div>
                <div class="value">{{ $clearance->project_cost ? 'P ' . number_format($clearance->project_cost, 2) : '' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">15. IS THE PROJECT APPLIED FOR THE SUBJECT OF WRITTEN NOTICE(S) FROM THIS BOARD OR THE LOCAL GOVERNMENT UNIT (LGU) TO PRESENT OR APPLY FOR LOCATIONAL CLEARANCE (LC)?</div>
                <div style="margin-top: 3px;">
                    <span class="checkbox">{{ $clearance->has_written_notice ? '✓' : '' }}</span> If Yes <span style="font-size: 8px;">(Please indicate the following)</span>
                    <span class="checkbox" style="margin-left: 30px;">{{ !$clearance->has_written_notice ? '✓' : '' }}</span> No
                </div>
                <div style="margin-left: 20px; margin-top: 3px;">
                    Issuing Officer: <span class="underline" style="min-width: 250px;">{{ $clearance->notice_issuing_officer }}</span><br>
                    Order in the Notice: <span class="underline" style="min-width: 250px;">{{ $clearance->notice_order }}</span><br>
                    Date of notice: <span class="underline" style="min-width: 250px;">{{ optional($clearance->notice_date)->format('M d, Y') }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">16. IS THE PROJECT APPLIED FOR THE SUBJECT OF RELATED ACTION(S) WITH OTHER OFFICES OF THE BOARD AND/OR LOCAL GOVERNMENT UNIT?</div>
                <div style="margin-top: 3px;">
                    <span class="checkbox">{{ $clearance->has_related_action ? '✓' : '' }}</span> If Yes <span style="font-size: 8px;">(Please indicate the following)</span>
                    <span class="checkbox" style="margin-left: 30px;">{{ !$clearance->has_related_action ? '✓' : '' }}</span> No
                </div>
                <div style="margin-left: 20px; margin-top: 3px;">
                    Office where Similar Action(s) was filed: <span class="underline" style="min-width: 250px;">{{ $clearance->action_office }}</span><br>
                    Date Filed: <span class="underline" style="min-width: 250px;">{{ optional($clearance->action_date_filed)->format('M d, Y') }}</span><br>
                    Action Taken: <span class="underline" style="min-width: 250px;">{{ $clearance->action_taken }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">17. PREFERRED MODE OR RELEASE OF DECISION</div>
                <div style="margin-top: 3px;">
                    <span class="checkbox">{{ $clearance->release_mode == 'pick_up' ? '✓' : '' }}</span> Pick-up
                    <span class="checkbox" style="margin-left: 15px;">{{ $clearance->release_mode == 'mail' ? '✓' : '' }}</span> By mail addressed to: 
                    <span class="checkbox" style="margin-left: 15px;">{{ $clearance->release_recipient == 'applicant' ? '✓' : '' }}</span> Applicant
                    <span class="checkbox" style="margin-left: 15px;">{{ $clearance->release_recipient == 'authorized_rep' ? '✓' : '' }}</span> Authorized Representative
                </div>
            </td>
        </tr>
        <tr>
            <td height="40px">
                <div class="label">18. SIGNATURE OF APPLICANT</div>
            </td>
            <td height="40px">
                <div class="label">19. SIGNATURE OF AUTHORIZED REPRESENTATIVE</div>
            </td>
        </tr>
    </table>

    <!-- Notary Section -->
    <div class="notary-section">
        <p>Republic of the Philippines) <br> _______________________ )S.S</p>
        <p style="text-indent: 30px; text-align: justify;">
            SUBCRIBED AND SWORN TO before me this ________ day of ____________________ in the Municipality of Malalag, Province of Davao del Sur. Affiant his/her Community Tax Certificate No. _________________ Issued at ____________________ on ____________________.
        </p>
        
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td width="50%">
                    Doc No. _________________<br>
                    Page No. _________________<br>
                    Book No. _________________<br>
                    Series No. _________________
                </td>
                <td width="50%" class="text-center" style="vertical-align: bottom;">
                    <strong>NOTARY PUBLIC</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>