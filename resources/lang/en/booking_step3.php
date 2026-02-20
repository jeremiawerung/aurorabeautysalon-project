<?php

return [
    // Page & Section Titles
    'select_reservation' => 'Select Reservation',
    'select_reservation_desc' => 'The reservations below are the ones you have selected and are not yet fully paid.',
    'payment_confirmation' => 'Payment Confirmation',
    'payment_confirmation_desc' => 'Choose payment type and method, then confirm the total before proceeding.',
    'payment_method' => 'Payment Method',
    'payment_method_desc' => 'All online payments are securely processed through Midtrans Snap.',

    // Reservation Card
    'reservation' => 'Reservation',
    'total_bill' => 'Total bill',
    'already_paid' => 'Already paid',
    'remaining' => 'Remaining',
    'standard_dp' => 'Standard DP',
    'nominal' => 'Nominal',

    // Payment Type
    'pay_dp' => 'Pay DP',
    'pay_dp_desc' => 'For new reservations, pay DP. Those already with DP will be settled.',
    'pay_full' => 'Pay in Full',
    'pay_full_desc' => 'Pay the entire remaining bill from your selected reservations.',
    'dp_disabled_note' => 'The "Pay DP" option is disabled because you selected a reservation with Down Payment status. The next payment will be calculated as settlement (pay remaining).',

    // Payment Method Options
    'online_payment' => 'Online Payment',
    'online_payment_desc' => 'Cards, bank transfer, e-wallet, and other supported methods.',
    'no_payment_method' => 'No active payment method available.',

    // Total & Button
    'total_to_pay' => 'Total to Pay',
    'total_calculated_note' => 'The total above has been calculated based on DP and settlement according to your selection.',
    'continue_payment' => 'Continue Payment',
    'back_to_history' => 'Back to Reservation History',
    'select_reservation_btn' => 'Select Reservation',

    // Notes
    'note' => 'Note',
    'note_dp' => 'If you choose DP payment type, reservations that have never been paid will be charged DP.',
    'note_settlement' => 'For reservations with Down Payment status, the next payment will always be the remaining balance settlement.',
    'no_reservation' => 'No reservations to pay at this time. Please return to the service selection page.',

    // Loading & Processing
    'processing' => 'Processing transaction, please wait...',
    'processing_reservations' => 'Processing :count reservations (:amount), please wait...',
    'payment_success_saving' => 'Payment **successful**! Saving data for :count reservations to database...',
    'payment_pending_saving' => 'Payment **pending**! Saving data for :count reservations to database...',

    // SweetAlert Messages
    'no_bill_title' => 'No Bill Yet',
    'no_bill_text' => 'Please select at least one reservation that still has a bill.',
    'select_method_title' => 'Payment Method',
    'select_method_text' => 'Please select a payment method.',
    'failed' => 'Failed',
    'failed_token' => 'Failed to create Midtrans payment token.',
    'save_failed_title' => 'Failed to Save History',
    'save_failed_text' => 'Payment successful, but failed to update reservation history. Please contact admin.',
    'pending_title' => 'Payment Pending',
    'pending_text' => 'Transaction created successfully (:status). Check history for payment instructions.',
    'save_pending_failed' => 'Payment pending, but failed to update reservation history. Please contact admin.',
    'error_title' => 'Payment Failed',
    'error_text' => 'An error occurred while processing payment.',
    'cancelled_title' => 'Payment Cancelled',
    'cancelled_text' => 'You closed the Midtrans payment pop-up. Reservation status unchanged.',
    'server_error_title' => 'Server Error',
    'server_error_text' => 'A server error occurred while processing payment.',

    'use_voucher' => 'Use Discount Voucher',
    'use_voucher_desc' => 'Save more with discount voucher',
    'no_voucher' => 'No Voucher',
    'no_voucher_desc' => 'Continue without discount',
    'yes_voucher' => 'Use Voucher',
    'yes_voucher_desc' => 'Enter voucher code',
    'voucher_code' => 'Voucher Code',
    'voucher_placeholder' => 'Example: LEBARAN2025',
    'apply' => 'Apply',
];
