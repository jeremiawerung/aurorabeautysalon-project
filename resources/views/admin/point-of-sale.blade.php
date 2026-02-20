@extends('layouts.app')

@section('title', 'Point of Sale - Booking Offline')

@push('styles')
<style>
:root {
    --primary: #d13a8a;
    --primary-dark: #b02e73;
    --primary-light: #fbe5f1;
    --border: #f1b8d6;
    --text: #333;
    --muted: #777;
    --bg: #f5f6fb;
}

* {
    box-sizing: border-box;
}

/* ===== NAVBAR DROPDOWN FIX - PENTING ===== */
nav.navbar {
    position: relative;
    z-index: 1030;
}

.navbar .dropdown-menu {
    position: absolute;
    z-index: 1040;
}

/* Pastikan navbar collapse tidak terpotong */
.navbar-collapse {
    overflow: visible !important;
}

/* Prevent horizontal scroll on entire page */
html {
    overflow-x: hidden;
    width: 100%;
}

body {
    overflow-x: hidden;
    width: 100%;
    position: relative;
}

/* Navbar wrapper - tidak boleh overflow hidden */
.pos-wrapper {
    width: 100%;
    overflow-x: hidden;
    position: relative;
}

/* Container fluid fix */
.container-fluid {
    overflow: visible !important;
    max-width: 100vw;
}
/* ===== END NAVBAR FIX ===== */

.content-wrapper {
    padding: 16px;
    background: var(--bg);
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .content-wrapper {
        padding: 24px;
    }
}

.pos-container {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    overflow-x: hidden;
}

/* Cards */
.card {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: #fff;
    margin-bottom: 16px;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .card {
        margin-bottom: 20px;
    }
}

.card-header {
    background: #fff;
    border-bottom: 1px solid rgba(241, 184, 214, .6);
    padding: 0.875rem 1rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 15px;
    width: 100%;
}

@media (min-width: 768px) {
    .card-header {
        padding: 1rem 1.5rem;
        font-size: 16px;
    }
}

.card-header small {
    color: var(--muted);
    font-weight: 400;
    font-size: 12px;
    width: 100%;
    margin-top: 4px;
}

@media (min-width: 640px) {
    .card-header small {
        width: auto;
        margin-top: 0;
        font-size: 13px;
    }
}

.card-body {
    padding: 1rem;
    width: 100%;
    overflow-x: hidden;
}

@media (min-width: 768px) {
    .card-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }
}

/* Layout */
.section-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    align-items: start;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 1024px) {
    .section-grid {
        grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
        gap: 20px;
    }
}

@media (min-width: 1200px) {
    .section-grid {
        grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.2fr);
    }
}

/* Prevent overflow in grid children */
.section-grid > * {
    min-width: 0;
    max-width: 100%;
}

/* Kategori & layanan */
.category-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
    margin-bottom: 16px;
    border-bottom: 1px dashed var(--border);
    padding-bottom: 10px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    width: 100%;
}

.category-tabs::-webkit-scrollbar {
    height: 4px;
}

.category-tabs::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.category-tabs::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 10px;
}

.category-tabs::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

@media (min-width: 768px) {
    .category-tabs {
        gap: 8px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        flex-wrap: wrap;
    }
}

.category-tab {
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 12px;
    color: var(--muted);
    white-space: nowrap;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .category-tab {
        padding: 9px 16px;
        font-size: 13px;
    }
}

.category-tab:hover {
    background: var(--primary-light);
    color: var(--text);
    transform: translateY(-1px);
}

.category-tab.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.service-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    width: 100%;
}

@media (min-width: 480px) {
    .service-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }
}

@media (min-width: 768px) {
    .service-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }
}

@media (min-width: 1024px) {
    .service-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

.service-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .service-card {
        border-radius: 14px;
        padding: 12px;
        gap: 10px;
    }
}

.service-card:hover {
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    transform: translateY(-3px);
    border-color: var(--primary);
}

.service-card.selected {
    border-color: var(--primary);
    background: var(--primary-light);
}

.service-card-image {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 10px;
}

@media (min-width: 768px) {
    .service-card-image {
        border-radius: 12px;
    }
}

.service-card-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    width: 100%;
    overflow: hidden;
}

@media (min-width: 768px) {
    .service-card-content {
        gap: 4px;
    }
}

.service-card .title {
    font-weight: 600;
    font-size: 13px;
    line-height: 1.3;
    color: var(--text);
    word-wrap: break-word;
    overflow-wrap: break-word;
}

@media (min-width: 768px) {
    .service-card .title {
        font-size: 14px;
    }
}

.service-card .meta {
    font-size: 10px;
    color: var(--muted);
    line-height: 1.4;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .service-card .meta {
        font-size: 11px;
    }
}

.service-card .price {
    color: var(--primary);
    font-weight: 700;
    font-size: 14px;
    margin-top: 2px;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .service-card .price {
        font-size: 15px;
    }
}

/* Cart */
.cart-item {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    width: 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .cart-item {
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        gap: 12px;
    }
}

.cart-item-info {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.cart-item-title {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 4px;
    color: var(--text);
    word-wrap: break-word;
    overflow-wrap: break-word;
}

@media (min-width: 768px) {
    .cart-item-title {
        font-size: 14px;
    }
}

.cart-item-meta {
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 4px;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .cart-item-meta {
        font-size: 12px;
    }
}

.cart-item-schedule {
    font-size: 11px;
    color: var(--primary-dark);
    font-weight: 500;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .cart-item-schedule {
        font-size: 12px;
    }
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-end;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .cart-item-actions {
        gap: 8px;
    }
}

.btn-schedule {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 7px 12px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s ease;
}

@media (min-width: 768px) {
    .btn-schedule {
        padding: 8px 14px;
        font-size: 12px;
    }
}

.btn-schedule:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.btn-remove {
    background: #fee2e2;
    color: #b91c1c;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .btn-remove {
        width: 30px;
        height: 30px;
        font-size: 18px;
    }
}

.btn-remove:hover {
    background: #fecaca;
    transform: scale(1.1);
}

.empty-state {
    text-align: center;
    color: #9ca3af;
    padding: 40px 16px;
    font-size: 13px;
    line-height: 1.5;
}

@media (min-width: 768px) {
    .empty-state {
        padding: 50px 20px;
        font-size: 14px;
    }
}

/* Modal */
.pos-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
    overflow-y: auto;
}

@media (min-width: 768px) {
    .pos-modal-backdrop {
        padding: 20px;
    }
}

.pos-modal-backdrop.show {
    display: flex;
}

.pos-modal {
    background: #fff;
    border-radius: 16px;
    max-width: 820px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    margin: auto;
}

@media (min-width: 768px) {
    .pos-modal {
        border-radius: 18px;
    }
}

.modal-header {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .modal-header {
        padding: 18px 24px;
        font-size: 17px;
    }
}

.modal-header button {
    border: none;
    background: transparent;
    font-size: 22px;
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .modal-header button {
        font-size: 24px;
        width: 32px;
        height: 32px;
    }
}

.modal-header button:hover {
    background: #f3f4f6;
}

.modal-body {
    padding: 16px;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
}

@media (min-width: 768px) {
    .modal-body {
        padding: 20px 24px;
    }
}

.modal-footer {
    padding: 14px 16px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: #fafafa;
    flex-wrap: wrap;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .modal-footer {
        padding: 16px 24px;
        gap: 10px;
        flex-wrap: nowrap;
    }
}

/* Date Grid */
.date-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 14px;
    width: 100%;
}

@media (min-width: 480px) {
    .date-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
}

@media (min-width: 640px) {
    .date-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    .date-grid {
        grid-template-columns: repeat(7, minmax(0, 1fr));
        margin-bottom: 16px;
    }
}

.date-item {
    padding: 10px 6px;
    border: 1px solid var(--border);
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #f9fafb;
    width: 100%;
}

@media (min-width: 768px) {
    .date-item {
        padding: 12px 8px;
        border-radius: 12px;
    }
}

.date-item:hover {
    border-color: var(--primary);
    background: var(--primary-light);
    transform: translateY(-2px);
}

.date-item.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.date-item .dw {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
}

@media (min-width: 768px) {
    .date-item .dw {
        font-size: 11px;
    }
}

.date-item .dd {
    font-size: 15px;
    font-weight: 700;
    margin-top: 3px;
}

@media (min-width: 768px) {
    .date-item .dd {
        font-size: 16px;
        margin-top: 4px;
    }
}

.date-item .mon {
    font-size: 9px;
    margin-top: 2px;
    opacity: 0.8;
}

@media (min-width: 768px) {
    .date-item .mon {
        font-size: 10px;
        margin-top: 3px;
    }
}

/* Time list */
.time-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    width: 100%;
}

@media (min-width: 768px) {
    .time-list {
        gap: 10px;
        max-height: 400px;
    }
}

.time-slot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
}

@media (min-width: 768px) {
    .time-slot {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
    }
}

.time-slot:hover {
    border-color: var(--primary);
    background: var(--primary-light);
    transform: translateX(4px);
}

.time-slot.active {
    border-color: var(--primary);
    background: var(--primary-light);
    font-weight: 600;
}

.time-slot.disabled {
    cursor: not-allowed;
    opacity: 0.4;
    background: #f3f4f6;
}

.time-slot.disabled:hover {
    transform: none;
}

/* Forms */
.form-group {
    margin-bottom: 14px;
    width: 100%;
}

@media (min-width: 768px) {
    .form-group {
        margin-bottom: 16px;
    }
}

.form-label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
    font-size: 12px;
    color: var(--text);
}

@media (min-width: 768px) {
    .form-label {
        margin-bottom: 6px;
        font-size: 13px;
    }
}

.form-control,
.form-select {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 9px 12px;
    width: 100%;
    max-width: 100%;
    font-size: 13px;
    transition: all 0.2s ease;
    background: #fff;
}

@media (min-width: 768px) {
    .form-control,
    .form-select {
        padding: 10px 13px;
        font-size: 14px;
    }
}

.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(209, 58, 138, .1);
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    width: 100%;
}

@media (min-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
}

/* Customer search */
.customer-wrapper {
    position: relative;
    width: 100%;
}

.customer-suggestions {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 4px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 14px 35px rgba(15, 23, 42, .18);
    max-height: 240px;
    overflow-y: auto;
    z-index: 50;
    width: 100%;
}

@media (min-width: 768px) {
    .customer-suggestions {
        margin-top: 6px;
        border-radius: 12px;
        max-height: 280px;
    }
}

.customer-suggestions-item {
    padding: 9px 12px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 3px;
    border-bottom: 1px solid #f3f4f6;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .customer-suggestions-item {
        padding: 10px 14px;
        font-size: 13px;
    }
}

.customer-suggestions-item:last-child {
    border-bottom: none;
}

.customer-suggestions-item span:first-child {
    font-weight: 600;
    color: var(--text);
}

.customer-suggestions-item span:last-child {
    color: var(--muted);
    font-size: 11px;
}

@media (min-width: 768px) {
    .customer-suggestions-item span:last-child {
        font-size: 12px;
    }
}

.customer-suggestions-item:hover {
    background: #f9fafb;
}

.customer-suggestions-empty {
    padding: 12px;
    font-size: 12px;
    color: var(--muted);
    text-align: center;
}

@media (min-width: 768px) {
    .customer-suggestions-empty {
        padding: 14px;
        font-size: 13px;
    }
}

/* Buttons */
.btn-primary {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 12px 18px;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    font-size: 13px;
    transition: all 0.2s ease;
}

@media (min-width: 768px) {
    .btn-primary {
        padding: 14px 20px;
        font-size: 14px;
    }
}

.btn-primary:hover:not(:disabled) {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(209, 58, 138, 0.3);
}

.btn-primary:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #6b7280;
    color: #fff;
    border: none;
    padding: 12px 18px;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
}

@media (min-width: 768px) {
    .btn-secondary {
        padding: 14px 20px;
        font-size: 14px;
    }
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

/* Total row */
.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0 0;
    border-top: 2px dashed var(--border);
    margin-top: 10px;
    font-weight: 700;
    font-size: 16px;
    color: var(--text);
    width: 100%;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .total-row {
        padding: 16px 0 0;
        margin-top: 12px;
        font-size: 17px;
    }
}

/* Table DP */
.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    max-width: 100%;
}

.dp-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    min-width: 600px;
}

@media (min-width: 768px) {
    .dp-table {
        margin-top: 12px;
    }
}

.dp-table th,
.dp-table td {
    padding: 10px 8px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
    font-size: 12px;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .dp-table th,
    .dp-table td {
        padding: 14px 12px;
        font-size: 13px;
    }
}

.dp-table th {
    background: var(--primary-light);
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
}

.dp-table tbody tr {
    transition: all 0.2s ease;
}

.dp-table tbody tr:hover {
    background: #fafafa;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 600;
}

@media (min-width: 768px) {
    .badge {
        padding: 5px 12px;
        font-size: 11px;
    }
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.btn-pay {
    padding: 6px 12px;
    margin-right: 4px;
    margin-bottom: 4px;
    font-size: 9px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-block;
}

@media (min-width: 768px) {
    .btn-pay {
        padding: 7px 14px;
        margin-right: 6px;
        margin-bottom: 6px;
        font-size: 10px;
    }
}

.btn-pay-midtrans {
    background: #00aadb;
    color: #fff;
}

.btn-pay-midtrans:hover {
    background: #0088b3;
    transform: translateY(-1px);
}

.btn-pay-cash {
    background: #10b981;
    color: #fff;
}

.btn-pay-cash:hover {
    background: #059669;
    transform: translateY(-1px);
}

/* Loading */
.loading-indicator {
    text-align: center;
    color: var(--muted);
    padding: 24px 16px;
    font-size: 12px;
}

@media (min-width: 768px) {
    .loading-indicator {
        padding: 30px 20px;
        font-size: 13px;
    }
}

/* Helper text */
.helper-text {
    font-size: 10px;
    color: #6b7280;
    display: block;
    margin-top: 5px;
    line-height: 1.4;
}

@media (min-width: 768px) {
    .helper-text {
        font-size: 11px;
        margin-top: 6px;
    }
}

/* Spacing utilities */
.mb-3 {
    margin-bottom: 14px;
}

@media (min-width: 768px) {
    .mb-3 {
        margin-bottom: 16px;
    }
}

.mt-3 {
    margin-top: 14px;
}

@media (min-width: 768px) {
    .mt-3 {
        margin-top: 16px;
    }
}

/* Header responsive */
.pos-container > h4 {
    font-weight: 700;
    font-size: 20px;
    color: var(--text);
    margin-bottom: 10px;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .pos-container > h4 {
        font-size: 24px;
        margin-bottom: 12px;
    }
}

.pos-container > p {
    color: #6b7280;
    font-size: 13px;
    margin-bottom: 20px;
    line-height: 1.6;
    word-wrap: break-word;
}

@media (min-width: 768px) {
    .pos-container > p {
        font-size: 14px;
        margin-bottom: 24px;
    }
}

/* Modal footer buttons responsive */
@media (max-width: 767px) {
    .modal-footer button {
        width: 100%;
        padding: 12px 20px !important;
    }
}

/* Prevent text overflow */
* {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Additional overflow fixes */
#cartList {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

#serviceGrid {
    width: 100%;
    max-width: 100%;
}

#categoryTabs {
    width: 100%;
    max-width: 100%;
}

/* Fix for very small screens */
@media (max-width: 360px) {
    .content-wrapper {
        padding: 12px;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .service-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 8px;
    }
    
    .cart-item {
        padding: 10px;
        gap: 8px;
    }
    
    .btn-schedule {
        padding: 6px 10px;
        font-size: 10px;
    }
    
    .form-control,
    .form-select {
        padding: 8px 10px;
        font-size: 12px;
    }
}

/* Landscape mobile fix */
@media (max-height: 500px) and (orientation: landscape) {
    .pos-modal {
        max-height: 95vh;
    }
    
    .modal-body {
        padding: 12px 16px;
    }
    
    .time-list {
        max-height: 200px;
    }
}

/* Tablet specific adjustments */
@media (min-width: 768px) and (max-width: 1023px) {
    .service-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .section-grid {
        gap: 18px;
    }
}

/* Large desktop */
@media (min-width: 1400px) {
    .pos-container {
        padding: 0 20px;
    }
}

/* Print styles */
@media print {
    .content-wrapper {
        padding: 0;
    }
    
    .btn-schedule,
    .btn-remove,
    .btn-primary,
    .btn-secondary,
    .category-tabs {
        display: none;
    }
    
    .card {
        box-shadow: none;
        page-break-inside: avoid;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .card {
        border-width: 2px;
    }
    
    .service-card {
        border-width: 2px;
    }
    
    .category-tab,
    .btn-schedule,
    .btn-primary {
        border-width: 2px;
        border-style: solid;
    }
}

/* Fix scrollbar styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c0c0c0;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

/* Firefox scrollbar */
* {
    scrollbar-width: thin;
    scrollbar-color: #c0c0c0 #f1f1f1;
}

/* Focus visible for keyboard navigation */
button:focus-visible,
.service-card:focus-visible,
.date-item:focus-visible,
.time-slot:focus-visible {
    outline: 3px solid var(--primary);
    outline-offset: 2px;
}

/* Fix iOS input zoom */
@media screen and (max-width: 767px) {
    input[type="text"],
    input[type="email"],
    input[type="password"],
    select,
    textarea {
        font-size: 16px !important;
    }
}

/* Prevent horizontal scroll on entire page */
html {
    overflow-x: hidden;
    width: 100%;
}

body {
    overflow-x: hidden;
    width: 100%;
    position: relative;
}

/* Container fluid fix */
.container-fluid {
    overflow-x: hidden;
    max-width: 100vw;
}

/* Image loading fix */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* Flexbox overflow fix */
.card-header > span,
.cart-item-title,
.service-card .title {
    min-width: 0;
    flex: 1;
}

/* Grid item overflow prevention */
.service-grid > *,
.date-grid > * {
    min-width: 0;
    max-width: 100%;
}

/* Safe area insets for notched devices */
@supports (padding: max(0px)) {
    .content-wrapper {
        padding-left: max(16px, env(safe-area-inset-left));
        padding-right: max(16px, env(safe-area-inset-right));
        padding-bottom: max(16px, env(safe-area-inset-bottom));
    }
    
    @media (min-width: 768px) {
        .content-wrapper {
            padding-left: max(24px, env(safe-area-inset-left));
            padding-right: max(24px, env(safe-area-inset-right));
            padding-bottom: max(24px, env(safe-area-inset-bottom));
        }
    }
}

/* Modal safe area */
@supports (padding: max(0px)) {
    .pos-modal-backdrop {
        padding-left: max(16px, env(safe-area-inset-left));
        padding-right: max(16px, env(safe-area-inset-right));
        padding-bottom: max(16px, env(safe-area-inset-bottom));
    }
}

/* Loading state improvements */
.loading-indicator {
    width: 100%;
    max-width: 100%;
}

/* Empty state improvements */
#cartList > .empty-state {
    width: 100%;
    box-sizing: border-box;
}

/* Table responsive improvements */
.dp-table td,
.dp-table th {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (min-width: 768px) {
    .dp-table td,
    .dp-table th {
        max-width: none;
    }
}

/* Action column fix */
.dp-table td:last-child {
    white-space: normal;
}

/* Button group wrapping */
.modal-footer,
.cart-item-actions {
    flex-wrap: wrap;
}

@media (min-width: 640px) {
    .cart-item-actions {
        flex-wrap: nowrap;
    }
}

/* Ensure proper stacking context */
.pos-modal-backdrop {
    isolation: isolate;
}

.customer-suggestions {
    isolation: isolate;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

@media (prefers-reduced-motion: reduce) {
    html {
        scroll-behavior: auto;
    }
}

/* Better touch targets for mobile */
@media (max-width: 767px) {
    button,
    .service-card,
    .category-tab,
    .date-item,
    .time-slot {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Fix for iOS Safari bottom bar */
@supports (-webkit-touch-callout: none) {
    .content-wrapper {
        min-height: -webkit-fill-available;
    }
}


</style>
@endpush

@section('content')
<div class="pos-wrapper"> 
    <div class="content-wrapper">
        <div class="pos-container">
            <div class="section-grid">
                {{-- Kolom Kiri: Pilih Layanan --}}
                <div>
                    <div class="card">
                        <div class="card-header">
                            <span>Pilih Layanan</span>
                            <small>Klik untuk menambahkan ke keranjang</small>
                        </div>
                        <div class="card-body">
                            <div class="category-tabs" id="categoryTabs">
                                <button class="category-tab active" data-category="all">Semua</button>
                                @foreach($categories as $cat)
                                    <button
                                        class="category-tab"
                                        data-category="{{ $cat->id_kategoriLayanan }}"
                                    >
                                        {{ $cat->nama }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="service-grid" id="serviceGrid">
                                @foreach($categories as $cat)
                                    @foreach($cat->layanan as $svc)
                                        @php
                                            $image = $svc->gambar
                                                ? asset('storage/'.$svc->gambar)
                                                : asset('img/favicon.svg');
                                        @endphp
                                        <div
                                            class="service-card"
                                            data-id="{{ $svc->id_layanan }}"
                                            data-category="{{ $cat->id_kategoriLayanan }}"
                                            data-price="{{ $svc->harga }}"
                                            data-duration="{{ $svc->durasi }}"
                                            data-title="{{ $svc->nama_layanan }}"
                                            data-image="{{ $image }}"
                                        >
                                            <img src="{{ $image }}" alt="{{ $svc->nama_layanan }}" class="service-card-image">
                                            <div class="service-card-content">
                                                <div class="title">{{ $svc->nama_layanan }}</div>
                                                <div class="meta">
                                                    {{ $cat->nama ?? 'Umum' }} • {{ $svc->durasi }} menit
                                                </div>
                                                <div class="price">
                                                    Rp {{ number_format($svc->harga, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Pelanggan, Keranjang, Checkout --}}
                <div>
                    <div class="card">
                        <div class="card-header">
                            <span>Detail Pelanggan & Booking</span>
                        </div>
                        <div class="card-body">

                            {{-- Pelanggan --}}
                            <div class="form-group">
                                <label class="form-label">Cari / Tambah Pelanggan</label>
                                <div class="customer-wrapper">
                                    <input
                                        type="text"
                                        id="pelangganNama"
                                        class="form-control"
                                        placeholder="Ketik nama pelanggan..."
                                        autocomplete="off"
                                    >
                                    <input type="hidden" id="pelangganId">
                                    <div id="customerSuggestions" class="customer-suggestions" style="display:none;"></div>
                                </div>
                                <small class="helper-text">
                                    Ketik nama untuk mencari. Jika tidak ada, data baru akan dibuat otomatis.
                                </small>
                            </div>

                            <div class="form-grid-2 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input
                                        type="text"
                                        id="pelangganTelepon"
                                        class="form-control"
                                        placeholder="08xxxxxxxxxx"
                                    >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email (opsional)</label>
                                    <input
                                        type="email"
                                        id="pelangganEmail"
                                        class="form-control"
                                        placeholder="email@contoh.com"
                                    >
                                </div>
                            </div>

                            {{-- Keranjang --}}
                            <div class="form-group">
                                <label class="form-label">Keranjang Layanan</label>
                                <div id="cartList" style="min-height: 180px;">
                                    <div class="empty-state">
                                        Belum ada layanan dipilih.<br>Klik layanan di sebelah kiri untuk menambahkan.
                                    </div>
                                </div>
                            </div>

                            <div class="total-row">
                                <span>Total</span>
                                <span id="totalPrice">Rp 0</span>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select id="payType" class="form-select">
                                    <option value="dp">DP (Down Payment)</option>
                                    <option value="full">Bayar Penuh</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Metode Pembayaran</label>
                                <select id="payMethod" class="form-select">
                                    <option value="midtrans">Midtrans (Online)</option>
                                    <option value="cash">Tunai / Transfer Bank</option>
                                </select>
                            </div>

                            <div class="form-group" id="voucherSection" style="display: none;">
                                <label class="form-label">Voucher Diskon (Opsional)</label>
                                <div style="display: flex; gap: 8px;">
                                    <select id="voucherSelect" class="form-select" style="flex: 1;">
                                        <option value="">Pilih Voucher</option>
                                    </select>
                                    <button type="button" id="btnApplyVoucher" class="btn-secondary" style="width: auto; padding-inline: 20px;">
                                        Terapkan
                                    </button>
                                </div>
                                <div id="voucherInfo" style="display: none; margin-top: 12px; padding: 12px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px; color: #166534; font-size: 14px;">
                                        <span style="font-weight: 600;">✓ Voucher diterapkan:</span>
                                        <span id="voucherName"></span>
                                    </div>
                                    <div style="margin-top: 4px; color: #166534; font-size: 13px;">
                                        Diskon: <span id="voucherAmount"></span>
                                    </div>
                                    <button type="button" id="btnRemoveVoucher" style="margin-top: 8px; background: none; border: none; color: #dc2626; font-size: 12px; cursor: pointer; text-decoration: underline;">
                                        Hapus Voucher
                                    </button>
                                </div>
                            </div>

                            <button id="btnCheckout" class="btn-primary" disabled>
                                Proses Booking & Bayar
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Down Payment --}}
                    <div class="card">
                        <div class="card-header">
                            <span>Daftar Booking Offline</span>
                        </div>
                        <div class="card-body">
                            <div class="table-wrapper">
                                <table class="dp-table" id="dpTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Dibayar</th>
                                            <th>Sisa</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dpTableBody">
                                        <tr>
                                            <td colspan="7" class="loading-indicator">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Jadwal --}}
<div id="modalSchedule" class="pos-modal-backdrop">
    <div class="pos-modal">
        <div class="modal-header">
            <span id="modalTitle">Pilih Jadwal</span>
            <button type="button" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="date-picker-container">
                <label class="form-label">Pilih Tanggal</label>
                <div class="date-grid" id="dateGrid"></div>
            </div>

            <div class="mt-3">
                <label class="form-label">Pilih Waktu</label>
                <div class="time-list" id="timeList">
                    <div class="loading-indicator">
                        Pilih tanggal terlebih dahulu
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" style="width:auto; padding-inline:26px;" onclick="closeModal()">Batal</button>
            <button class="btn-primary" style="width:auto; padding-inline:26px;" onclick="applySchedule()">
                Simpan Jadwal
            </button>
        </div>
    </div>
</div>

{{-- Modal Verifikasi Password (Tunai) --}}
<div id="modalPassword" class="pos-modal-backdrop">
    <div class="pos-modal" style="max-width: 480px;">
        <div class="modal-header">
            <span>Verifikasi Admin</span>
            <button type="button" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 18px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                Masukkan password admin untuk melanjutkan pembayaran tunai/transfer bank.
            </p>
            <div class="form-group">
                <label class="form-label">Password Admin</label>
                <input
                    type="password"
                    id="adminPassword"
                    class="form-control"
                    placeholder="Masukkan password"
                >
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" style="width:auto; padding-inline:26px;" onclick="closePasswordModal()">Batal</button>
            <button class="btn-primary" style="width:auto; padding-inline:26px;" onclick="verifyAndPayCash()">
                Verifikasi & Bayar
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Midtrans Snap JS --}}
@php
    $snapUrl = config('midtrans.isProduction') 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = config('midtrans.clientKey');
@endphp

<script 
    type="text/javascript"
    src="{{ $snapUrl }}"
    data-client-key="{{ $clientKey }}">
</script>

<script>
const CSRF = '{{ csrf_token() }}';
const DATES = @json($dates);
const SAVE_URL = '{{ route("admin.pos.save") }}';
const SLOT_URL = '{{ route("booking.slot_jadwal") }}';
const MIDTRANS_PROSES_URL = '{{ route("admin.pos.midtrans.proses") }}';
const MIDTRANS_CALLBACK_URL = '{{ route("admin.pos.midtrans.callback") }}';
const CUSTOMER_SEARCH_URL = '{{ route("admin.pos.searchCustomer") }}';
const VERIFY_PASSWORD_URL = '{{ route("admin.pos.verifyPassword") }}';
const DP_LIST_URL = '{{ route("admin.pos.dpList") }}';
const PAY_REMAINING_URL = '{{ route("admin.pos.payRemaining") }}';
const VOUCHERS_AVAILABLE_URL = '{{ route("admin.pos.vouchers.available") }}';
const VOUCHERS_VALIDATE_URL = '{{ route("admin.pos.vouchers.validate") }}';

let cart = [];
let schedules = {};
let activeServiceId = null;
let tempSelected = { date: null, time: null };
let currentPaymentData = null;
let appliedVoucher = null;
let dpPercentage = {{ $pengaturan_booking->dp_value ?? 50 }};

/** SweetAlert Helpers */
function swAlert(type, text) {
    return Swal.fire({
        icon: type,
        text: text,
        confirmButtonColor: '#d13a8a',
        allowOutsideClick: false,
    });
}

function swConfirm(text) {
    return Swal.fire({
        icon: 'question',
        text: text,
        showCancelButton: true,
        confirmButtonColor: '#d13a8a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
    });
}

function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function debounce(func, wait = 350) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(null, args), wait);
    };
}

/* Kategori Filter */
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const cat = tab.dataset.category;
        document.querySelectorAll('.service-card').forEach(card => {
            if (cat === 'all' || card.dataset.category === cat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

/* Klik layanan -> tambah cart */
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', () => {
        const id = parseInt(card.dataset.id);
        if (cart.find(c => c.id === id)) {
            swAlert('warning', 'Layanan sudah ada di keranjang');
            return;
        }

        cart.push({
            id: id,
            title: card.dataset.title,
            price: parseFloat(card.dataset.price),
            duration: parseInt(card.dataset.duration),
            image: card.dataset.image
        });

        saveCartToStorage();
        card.classList.add('selected');
        setTimeout(() => card.classList.remove('selected'), 300);

        renderCart();
    });
});

function saveCartToStorage() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));
    localStorage.setItem('pos_schedules', JSON.stringify(schedules));
}

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('pos_cart');
    const savedSchedules = localStorage.getItem('pos_schedules');
    
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {
            cart = [];
        }
    }
    
    if (savedSchedules) {
        try {
            schedules = JSON.parse(savedSchedules);
        } catch (e) {
            schedules = {};
        }
    }
}

function clearCartStorage() {
    localStorage.removeItem('pos_cart');
    localStorage.removeItem('pos_schedules');
    cart = [];
    schedules = {};
}

function renderCart() {
    const list = document.getElementById('cartList');
    const btnCheckout = document.getElementById('btnCheckout');

    if (cart.length === 0) {
        list.innerHTML = '<div class="empty-state">Belum ada layanan dipilih.<br>Klik layanan di sebelah kiri untuk menambahkan.</div>';
        document.getElementById('totalPrice').textContent = 'Rp 0';
        btnCheckout.disabled = true;
        removeVoucher(); // Reset voucher jika cart kosong
        return;
    }

    let html = '';
    let total = 0;
    cart.forEach(item => {
        total += item.price;
        const sch = schedules[item.id];
        const schText = sch ? `${formatDate(sch.date)} • ${sch.time}` : 'Belum dijadwalkan';

        html += `
        <div class="cart-item">
            <div class="cart-item-info">
                <div class="cart-item-title">${item.title}</div>
                <div class="cart-item-meta">
                    Durasi: ${item.duration} menit • ${formatRupiah(item.price)}
                </div>
                <div class="cart-item-schedule">${schText}</div>
            </div>
            <div class="cart-item-actions">
                <button class="btn-schedule" type="button" onclick="openModal(${item.id})">
                    ${sch ? 'Ubah Jadwal' : 'Atur Jadwal'}
                </button>
                <button class="btn-remove" type="button" onclick="removeFromCart(${item.id})">&times;</button>
            </div>
        </div>`;
    });

    list.innerHTML = html;
    
    updateTotalDisplay(total);
    loadAvailableVouchers();

    const allScheduled = cart.every(c => schedules[c.id]);
    const hasCustomer = document.getElementById('pelangganId').value ||
        (document.getElementById('pelangganNama').value.trim() &&
         document.getElementById('pelangganTelepon').value.trim());

    btnCheckout.disabled = !(allScheduled && hasCustomer && cart.length > 0);
}

async function loadAvailableVouchers() {
    if (cart.length === 0) {
        document.getElementById('voucherSection').style.display = 'none';
        return;
    }

    const layananIds = cart.map(c => c.id);
    const payType = document.getElementById('payType').value;

    if (payType !== 'full') {
        document.getElementById('voucherSection').style.display = 'none';
        appliedVoucher = null;
        return;
    }

    try {
        const res = await fetch(VOUCHERS_AVAILABLE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ layanan_ids: layananIds })
        });

        const data = await res.json();

        if (data.success && data.data && data.data.length > 0) {
            const select = document.getElementById('voucherSelect');
            select.innerHTML = '<option value="">Pilih Voucher</option>';
            
            data.data.forEach(voucher => {
                const option = document.createElement('option');
                option.value = voucher.kode_diskon;
                option.textContent = `${voucher.nama_diskon} (${voucher.persentase_diskon}% OFF)`;
                option.dataset.voucherId = voucher.id;
                option.dataset.voucherName = voucher.nama_diskon;
                option.dataset.voucherPercentage = voucher.persentase_diskon;
                select.appendChild(option);
            });

            document.getElementById('voucherSection').style.display = 'block';
        } else {
            document.getElementById('voucherSection').style.display = 'none';
        }
    } catch (e) {
        console.error('Error loading vouchers:', e);
        document.getElementById('voucherSection').style.display = 'none';
    }
}

async function applyVoucher() {
    const select = document.getElementById('voucherSelect');
    const kodeDiskon = select.value;

    if (!kodeDiskon) {
        await swAlert('warning', 'Pilih voucher terlebih dahulu.');
        return;
    }

    const layananIds = cart.map(c => c.id);

    try {
        const res = await fetch(VOUCHERS_VALIDATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                kode_diskon: kodeDiskon,
                layanan_ids: layananIds
            })
        });

        const data = await res.json();

        if (data.success) {
            appliedVoucher = data.data;
            
            document.getElementById('voucherName').textContent = data.data.nama_diskon;
            document.getElementById('voucherAmount').textContent = formatRupiah(data.data.total_diskon);
            document.getElementById('voucherInfo').style.display = 'block';
            select.disabled = true;

            const total = cart.reduce((sum, item) => sum + item.price, 0);
            updateTotalDisplay(total);
            
            // SweetAlert sukses
            await swAlert('success', 'Voucher berhasil diterapkan!');
        } else {
            // SweetAlert error dari server
            await swAlert('error', data.message || 'Gagal menerapkan voucher.');
        }
    } catch (e) {
        console.error('Error applying voucher:', e);
        // SweetAlert error exception
        await swAlert('error', 'Terjadi kesalahan saat menerapkan voucher.');
    }
}

function removeVoucher() {
    appliedVoucher = null;
    document.getElementById('voucherInfo').style.display = 'none';
    document.getElementById('voucherSelect').disabled = false;
    document.getElementById('voucherSelect').value = '';
    document.getElementById('voucherSelect').innerHTML = '<option value="">Pilih Voucher</option>';
    
    const total = cart.reduce((sum, item) => sum + item.price, 0);
    updateTotalDisplay(total);
}

function updateTotalDisplay(totalAmount) {
    const payType = document.getElementById('payType').value;
    const totalPriceEl = document.getElementById('totalPrice');
    
    let displayAmount = totalAmount;
    
    if (payType === 'dp') {
        displayAmount = Math.round((totalAmount * dpPercentage) / 100);
        totalPriceEl.innerHTML = `
            ${formatRupiah(displayAmount)}
            <small style="display: block; font-size: 11px; font-weight: 400; color: #777; margin-top: 4px;">
                DP ${dpPercentage}% dari ${formatRupiah(totalAmount)}
            </small>
        `;
    } else {
        // Kurangi dengan diskon jika ada
        if (appliedVoucher && appliedVoucher.total_diskon) {
            const diskon = appliedVoucher.total_diskon;
            displayAmount = Math.max(0, totalAmount - diskon);
            
            totalPriceEl.innerHTML = `
                ${formatRupiah(displayAmount)}
                <small style="display: block; font-size: 11px; font-weight: 400; color: #16a34a; margin-top: 4px;">
                    Hemat ${formatRupiah(diskon)} dari ${formatRupiah(totalAmount)}
                </small>
            `;
        } else {
            totalPriceEl.textContent = formatRupiah(displayAmount);
        }
    }
}

document.getElementById('payType').addEventListener('change', () => {
    const payType = document.getElementById('payType').value;
    
    // Reset voucher jika bukan full payment
    if (payType !== 'full') {
        removeVoucher();
        document.getElementById('voucherSection').style.display = 'none';
    }
    
    const total = cart.reduce((sum, item) => sum + item.price, 0);
    updateTotalDisplay(total);
    loadAvailableVouchers();
});

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]}`;
}

function removeFromCart(id) {
    swConfirm('Hapus layanan ini dari keranjang?').then(res => {
        if (!res.isConfirmed) return;
        cart = cart.filter(c => c.id !== id);
        delete schedules[id];
        
        // Reset voucher karena layanan berubah
        if (appliedVoucher) {
            removeVoucher();
        }
        
        saveCartToStorage();
        renderCart();
    });
}

/* Modal Jadwal */
function openModal(serviceId) {
    activeServiceId = serviceId;
    const svc = cart.find(c => c.id === serviceId);
    document.getElementById('modalTitle').textContent = `Pilih Jadwal: ${svc?.title || ''}`;

    const existing = schedules[serviceId];
    if (existing) {
        tempSelected = { ...existing };
    } else {
        tempSelected = { date: null, time: null };
    }

    renderDateGrid();
    renderTimeList();

    const backdrop = document.getElementById('modalSchedule');
    backdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const backdrop = document.getElementById('modalSchedule');
    backdrop.classList.remove('show');
    document.body.style.overflow = '';
}

function renderDateGrid() {
    const grid = document.getElementById('dateGrid');
    grid.innerHTML = '';

    DATES.forEach(d => {
        const el = document.createElement('div');
        el.className = 'date-item' + (tempSelected.date === d.date ? ' active' : '');
        el.innerHTML = `
            <div class="dw">${d.dw}</div>
            <div class="dd">${d.dd}</div>
            <div class="mon">${d.mon}</div>
        `;
        el.onclick = () => {
            tempSelected.date = d.date;
            tempSelected.time = null;
            renderDateGrid();
            renderTimeList();
        };
        grid.appendChild(el);
    });
}

async function renderTimeList() {
    const list = document.getElementById('timeList');

    if (!tempSelected.date) {
        list.innerHTML = '<div class="loading-indicator">Pilih tanggal terlebih dahulu</div>';
        return;
    }

    list.innerHTML = '<div class="loading-indicator">Memuat slot waktu...</div>';

    try {
        const response = await fetch(`${SLOT_URL}?id_layanan=${activeServiceId}&date=${tempSelected.date}`);
        const data = await response.json();

        if (!data.success || !data.slots || data.slots.length === 0) {
            list.innerHTML = '<div class="loading-indicator">Tidak ada slot tersedia untuk tanggal ini</div>';
            return;
        }

        const disabledBySelf = [];
        Object.entries(schedules).forEach(([sid, sc]) => {
            if (parseInt(sid) !== activeServiceId && sc?.date === tempSelected.date && sc?.time) {
                disabledBySelf.push(sc.time);
            }
        });

        const now = new Date();
        const todayYMD = [
            now.getFullYear(),
            String(now.getMonth() + 1).padStart(2, '0'),
            String(now.getDate()).padStart(2, '0')
        ].join('-');

        list.innerHTML = '';
        data.slots.forEach(slot => {
            const isActive = tempSelected.time === slot.time;

            let isDisabled = !!slot.disabled || disabledBySelf.includes(slot.time);

            if (!isDisabled && tempSelected.date === todayYMD) {
                const [hStr, mStr] = slot.time.split(':');
                const slotDate = new Date();
                slotDate.setHours(parseInt(hStr), parseInt(mStr), 0, 0);

                if (slotDate <= now) {
                    isDisabled = true;
                }
            }

            const el = document.createElement('div');
            el.className =
                'time-slot' +
                (isActive ? ' active' : '') +
                (isDisabled ? ' disabled' : '');
            el.textContent = slot.time;

            if (!isDisabled) {
                el.onclick = () => {
                    tempSelected.time = slot.time;
                    renderTimeList();
                };
            }

            list.appendChild(el);
        });
    } catch (error) {
        console.error('Error loading time slots:', error);
        list.innerHTML = '<div class="loading-indicator">Gagal memuat slot waktu.</div>';
    }
}

function applySchedule() {
    if (!tempSelected.date || !tempSelected.time) {
        swAlert('warning', 'Pilih tanggal dan waktu terlebih dahulu.');
        return;
    }
    schedules[activeServiceId] = { ...tempSelected };
    saveCartToStorage();
    renderCart();
    closeModal();
}

/* Customer Search */
const namaInput = document.getElementById('pelangganNama');
const idInput = document.getElementById('pelangganId');
const telpInput = document.getElementById('pelangganTelepon');
const emailInput = document.getElementById('pelangganEmail');
const suggestionBox = document.getElementById('customerSuggestions');

const debouncedSearchCustomer = debounce(async (keyword) => {
    if (!keyword || keyword.length < 2) {
        suggestionBox.style.display = 'none';
        suggestionBox.innerHTML = '';
        return;
    }

    try {
        const res = await fetch(`${CUSTOMER_SEARCH_URL}?q=${encodeURIComponent(keyword)}`);
        const data = await res.json();

        if (!data.success) {
            suggestionBox.style.display = 'none';
            suggestionBox.innerHTML = '';
            return;
        }

        const customers = data.data || [];
        if (!customers.length) {
            suggestionBox.style.display = 'block';
            suggestionBox.innerHTML = `
                <div class="customer-suggestions-empty">
                    Tidak ditemukan. Data baru akan dibuat saat booking disimpan.
                </div>
            `;
            return;
        }

        suggestionBox.style.display = 'block';
        suggestionBox.innerHTML = customers.map(c => `
            <div class="customer-suggestions-item"
                 data-id="${c.id_pelanggan}"
                 data-nama="${c.nama}"
                 data-email="${c.email ?? ''}"
                 data-telp="${c.nomor_telepon ?? ''}">
                <span>${c.nama}</span>
                <span>${c.nomor_telepon ?? '-'} • ${c.email ?? '-'}</span>
            </div>
        `).join('');

        suggestionBox.querySelectorAll('.customer-suggestions-item').forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id;
                const nama = item.dataset.nama;
                const email = item.dataset.email;
                const telp = item.dataset.telp;

                idInput.value = id;
                namaInput.value = nama;
                telpInput.value = telp;
                emailInput.value = email;

                suggestionBox.style.display = 'none';
                suggestionBox.innerHTML = '';
                renderCart();
            });
        });

    } catch (e) {
        console.error('Error searching customer:', e);
        suggestionBox.style.display = 'none';
        suggestionBox.innerHTML = '';
    }
}, 400);

namaInput.addEventListener('input', (e) => {
    idInput.value = '';
    debouncedSearchCustomer(e.target.value.trim());
    renderCart();
});

[telpInput, emailInput].forEach(el => {
    el.addEventListener('input', renderCart);
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.customer-wrapper')) {
        suggestionBox.style.display = 'none';
    }
});

/* Checkout */
document.getElementById('btnCheckout').addEventListener('click', async () => {
    const pelangganId = idInput.value;
    const nama = namaInput.value.trim();
    const telp = telpInput.value.trim();
    const email = emailInput.value.trim();

    if (!pelangganId && !nama) {
        await swAlert('warning', 'Isi nama pelanggan terlebih dahulu.');
        return;
    }

    if (!telp) {
        const resTelp = await swConfirm('Nomor telepon kosong. Lanjutkan tanpa nomor telepon?');
        if (!resTelp.isConfirmed) {
            return;
        }
    }

    const allScheduled = cart.every(c => schedules[c.id]);
    if (!allScheduled) {
        await swAlert('warning', 'Semua layanan harus sudah dijadwalkan.');
        return;
    }

    if (cart.length === 0) {
        await swAlert('warning', 'Keranjang masih kosong.');
        return;
    }

    const payType = document.getElementById('payType').value;
    const payMethod = document.getElementById('payMethod').value;
    const totalAmount = cart.reduce((sum, item) => sum + item.price, 0);

    let confirmMsg = `Konfirmasi booking:\n\n` +
        `Pelanggan: ${nama}\n` +
        `Telepon: ${telp || '-'}\n` +
        `Layanan: ${cart.length} item\n` +
        `Total: ${formatRupiah(totalAmount)}\n`;
    
    if (appliedVoucher) {
        confirmMsg += `Diskon: ${formatRupiah(appliedVoucher.total_diskon)}\n`;
        confirmMsg += `Total Bayar: ${formatRupiah(totalAmount - appliedVoucher.total_diskon)}\n`;
    }
    
    confirmMsg += `Pembayaran: ${payType === 'dp' ? 'DP' : 'Lunas'}\n` +
        `Metode: ${payMethod === 'midtrans' ? 'Midtrans' : 'Tunai/Transfer'}\n\n` +
        `Lanjutkan?`;

    const confirmRes = await swConfirm(confirmMsg);
    if (!confirmRes.isConfirmed) {
        return;
    }

    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    currentPaymentData = {
        pelangganId,
        nama,
        telp,
        email,
        schedules,
        payType,
        payMethod,
        totalAmount
    };

    if (payMethod === 'midtrans') {
        await processMidtransPayment();
    } else {
        openPasswordModal();
        btn.disabled = false;
        btn.textContent = 'Proses Booking & Bayar';
    }
});

async function processMidtransPayment() {
    const btn = document.getElementById('btnCheckout');
    
    try {
        const saveRes = await saveBooking();
        if (!saveRes.success) {
            await swAlert('error', saveRes.message || 'Gagal menyimpan booking.');
            btn.disabled = false;
            btn.textContent = 'Proses Booking & Bayar';
            return;
        }

        const reservasiIds = saveRes.reservasi_ids;

        const requestBody = {
            reservasi_ids: reservasiIds,
            pay_type: currentPaymentData.payType,
            metode_id: 1
        };

        // Tambahkan diskon jika ada
        if (appliedVoucher) {
            requestBody.diskon_data = {
                diskon_id: appliedVoucher.diskon_id,
                kode_diskon: appliedVoucher.kode_diskon,
                total_diskon: appliedVoucher.total_diskon,
                applicable_layanan: appliedVoucher.applicable_layanan
            };
        }

        const midRes = await fetch(MIDTRANS_PROSES_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify(requestBody)
        });

        const midData = await midRes.json();

        if (!midData.success || !midData.snap_token) {
            await swAlert('error', midData.message || 'Gagal membuat transaksi Midtrans.');
            btn.disabled = false;
            btn.textContent = 'Proses Booking & Bayar';
            return;
        }

        const snapToken = midData.snap_token;
        btn.textContent = 'Menunggu pembayaran...';

        window.snap.pay(snapToken, {
            onSuccess: async function(result) {
                console.log('Payment success:', result);
                
                try {
                    const callbackBody = {
                        reservasi_ids: reservasiIds,
                        order_id: midData.order_id,
                        transaction_status: result.transaction_status || 'settlement',
                        metode_id: 1,
                        pay_type: currentPaymentData.payType
                    };

                    // Tambahkan diskon jika ada
                    if (appliedVoucher) {
                        callbackBody.diskon_data = {
                            diskon_id: appliedVoucher.diskon_id,
                            kode_diskon: appliedVoucher.kode_diskon,
                            total_diskon: appliedVoucher.total_diskon,
                            applicable_layanan: appliedVoucher.applicable_layanan
                        };
                    }

                    const callbackRes = await fetch(MIDTRANS_CALLBACK_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify(callbackBody)
                    });

                    const callbackData = await callbackRes.json();
                    
                    if (callbackData.success) {
                        clearCartStorage();
                        removeVoucher();
                        await swAlert('success', 'Pembayaran berhasil!');
                        loadDPList();
                        window.location.reload();
                    } else {
                        await swAlert('warning', 'Pembayaran berhasil tapi gagal menyimpan: ' + callbackData.message);
                        loadDPList();
                    }
                } catch (e) {
                    console.error('Callback error:', e);
                    await swAlert('warning', 'Pembayaran berhasil tapi gagal konfirmasi.');
                    loadDPList();
                }
            },
            onPending: async function(result) {
                console.log('Payment pending:', result);
                
                try {
                    const callbackBody = {
                        reservasi_ids: reservasiIds,
                        order_id: midData.order_id,
                        transaction_status: 'pending',
                        metode_id: 1,
                        pay_type: currentPaymentData.payType
                    };

                    if (appliedVoucher) {
                        callbackBody.diskon_data = {
                            diskon_id: appliedVoucher.diskon_id,
                            kode_diskon: appliedVoucher.kode_diskon,
                            total_diskon: appliedVoucher.total_diskon,
                            applicable_layanan: appliedVoucher.applicable_layanan
                        };
                    }

                    await fetch(MIDTRANS_CALLBACK_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify(callbackBody)
                    });
                } catch (e) {
                    console.error('Callback error:', e);
                }
                
                clearCartStorage();
                removeVoucher();
                await swAlert('info', 'Pembayaran pending. Status akan diupdate otomatis.');
                loadDPList();
                window.location.reload();
            },
            onError: function(result) {
                console.error('Payment error:', result);
                swAlert('error', 'Terjadi kesalahan saat memproses pembayaran.')
                    .then(() => {
                        btn.disabled = false;
                        btn.textContent = 'Proses Booking & Bayar';
                    });
            },
            onClose: function() {
                swAlert('warning', 'Pembayaran dibatalkan. Data tetap tersimpan, Anda bisa melanjutkan pembayaran dari tabel di bawah.')
                    .then(() => {
                        loadDPList();
                        btn.disabled = false;
                        btn.textContent = 'Proses Booking & Bayar';
                    });
            }
        });

    } catch (e) {
        console.error('Checkout error:', e);
        await swAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
        btn.disabled = false;
        btn.textContent = 'Proses Booking & Bayar';
    }
}

async function saveBooking() {
    const formData = new FormData();
    formData.append('_token', CSRF);
    formData.append('pelanggan_id', currentPaymentData.pelangganId || '');
    formData.append('pelanggan_nama', currentPaymentData.nama);
    formData.append('pelanggan_telepon', currentPaymentData.telp);
    formData.append('pelanggan_email', currentPaymentData.email);
    formData.append('schedules', JSON.stringify(currentPaymentData.schedules));
    formData.append('pay_type', currentPaymentData.payType);
    formData.append('metode_id', currentPaymentData.payMethod === 'midtrans' ? 1 : 2);

    const res = await fetch(SAVE_URL, { method: 'POST', body: formData });
    return await res.json();
}

/* Modal Password */
function openPasswordModal() {
    document.getElementById('adminPassword').value = '';
    const backdrop = document.getElementById('modalPassword');
    backdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closePasswordModal() {
    const backdrop = document.getElementById('modalPassword');
    backdrop.classList.remove('show');
    document.body.style.overflow = '';
}

async function verifyAndPayCash() {
    const password = document.getElementById('adminPassword').value.trim();
    
    if (!password) {
        await swAlert('warning', 'Password tidak boleh kosong.');
        return;
    }

    try {
        const verifyRes = await fetch(VERIFY_PASSWORD_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ password })
        });

        const verifyData = await verifyRes.json();

        if (!verifyData.success) {
            await swAlert('error', 'Password salah. Silakan coba lagi.');
            return;
        }

        closePasswordModal();

        const saveRes = await saveBooking();
        if (!saveRes.success) {
            await swAlert('error', saveRes.message || 'Gagal menyimpan booking.');
            return;
        }

        const reservasiIds = saveRes.reservasi_ids;

        const payRes = await fetch(PAY_REMAINING_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                reservasi_ids: reservasiIds,
                pay_type: currentPaymentData.payType,
                metode_id: 2
            })
        });

        const payData = await payRes.json();

        if (payData.success) {
            clearCartStorage();
            await swAlert('success', 'Pembayaran tunai berhasil!');
            loadDPList();
            window.location.reload();
        } else {
            await swAlert('error', payData.message || 'Gagal memproses pembayaran.');
        }

    } catch (e) {
        console.error('Payment error:', e);
        await swAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
    }
}

/* Load DP List */
async function loadDPList() {
    const tbody = document.getElementById('dpTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="loading-indicator">Memuat data...</td></tr>';

    try {
        const res = await fetch(DP_LIST_URL);
        const data = await res.json();

        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="loading-indicator">Tidak ada data booking offline yang belum lunas.</td></tr>';
            return;
        }

        let html = '';
        data.data.forEach(item => {
            const statusBadge = item.status_display === 'Belum Dibayar'
                ? '<span class="badge badge-warning">Belum Dibayar</span>'
                : item.status_display === 'DP'
                ? '<span class="badge badge-warning">DP</span>'
                : '<span class="badge" style="background: #dcfce7; color: #166534;">Lunas</span>';

            const actionButtons = item.remaining > 0 ? `
                <button class="btn-pay btn-pay-cash" onclick="payRemainingCash(${item.id_reservasi})">
                    Bayar Sisa
                </button>
            ` : '<span style="color: #10b981; font-weight: 600;">Lunas</span>';

            html += `
            <tr>
                <td>#${item.id_reservasi}</td>
                <td>${item.pelanggan_nama}</td>
                <td>${formatRupiah(item.total_harga)}</td>
                <td>${formatRupiah(item.total_paid)}</td>
                <td>${formatRupiah(item.remaining)}</td>
                <td>${statusBadge}</td>
                <td>${actionButtons}</td>
            </tr>`;
        });

        tbody.innerHTML = html;

    } catch (e) {
        console.error('Error loading DP list:', e);
        tbody.innerHTML = '<tr><td colspan="7" class="loading-indicator">Gagal memuat data.</td></tr>';
    }
}

async function payRemainingCash(reservasiId) {
    const { value: password } = await Swal.fire({
        title: 'Verifikasi Admin',
        input: 'password',
        inputLabel: 'Masukkan password admin',
        inputPlaceholder: 'Password',
        showCancelButton: true,
        confirmButtonColor: '#d13a8a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Verifikasi & Bayar',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Password tidak boleh kosong!';
            }
        }
    });

    if (!password) return;

    try {
        const verifyRes = await fetch(VERIFY_PASSWORD_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ password })
        });

        const verifyData = await verifyRes.json();

        if (!verifyData.success) {
            await swAlert('error', 'Password salah. Silakan coba lagi.');
            return;
        }

        const payRes = await fetch(PAY_REMAINING_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                reservasi_ids: [reservasiId],
                pay_type: 'full',
                metode_id: 2
            })
        });

        const payData = await payRes.json();

        if (payData.success) {
            await swAlert('success', 'Pembayaran tunai berhasil!');
            loadDPList();
        } else {
            await swAlert('error', payData.message || 'Gagal memproses pembayaran.');
        }

    } catch (e) {
        console.error('Payment error:', e);
        await swAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
    }
}

/* ESC untuk tutup modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closePasswordModal();
    }
});

document.getElementById('btnApplyVoucher').addEventListener('click', applyVoucher);
document.getElementById('btnRemoveVoucher').addEventListener('click', removeVoucher);

/* ESC untuk tutup modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closePasswordModal();
    }
});

// Initial load
loadCartFromStorage();
renderCart();
loadDPList();

// Initial load
loadCartFromStorage();
renderCart();
loadDPList();
</script>
@endpush