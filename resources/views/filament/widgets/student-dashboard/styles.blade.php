{{-- CSS Styles for both Light and Dark modes --}}
<style>
    .sdw-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .dark .sdw-card {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dark .sdw-card-title {
        color: #f3f4f6;
    }

    /* Icon sizing - proper sizing for mobile and desktop */
    .sdw-icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }

    .sdw-info-grid {
        display: grid;
        grid-template-columns: 1fr; /* Single column on mobile */
        gap: 1rem;
    }

    /* Two columns on larger screens */
    @media (min-width: 640px) {
        .sdw-info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Auto-fit for even larger screens */
    @media (min-width: 1024px) {
        .sdw-info-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }

    .sdw-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .sdw-info-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dark .sdw-info-label {
        color: #9ca3af;
    }

    .sdw-info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .dark .sdw-info-value {
        color: #f3f4f6;
    }

    .sdw-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .sdw-badge-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .dark .sdw-badge-success {
        background-color: rgba(21, 128, 61, 0.2);
        color: #4ade80;
    }

    .sdw-badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .dark .sdw-badge-warning {
        background-color: rgba(146, 64, 14, 0.2);
        color: #fbbf24;
    }

    .sdw-badge-info {
        background-color: #e0e7ff;
        color: #3730a3;
    }

    .dark .sdw-badge-info {
        background-color: rgba(55, 48, 163, 0.2);
        color: #a5b4fc;
    }

    /* Track Schedule Horizontal Table */
    .sdw-schedule-container {
        overflow-x: auto;
        margin: 0 -0.25rem;
        padding: 0.25rem;
    }

    .sdw-schedule-table {
        display: flex;
        flex-direction: row;
        min-width: max-content;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .dark .sdw-schedule-table {
        border-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-schedule-cell {
        display: flex;
        flex-direction: column;
        min-width: 80px;
        border-right: 1px solid #e5e7eb;
    }

    .sdw-schedule-cell:last-child {
        border-right: none;
    }

    .dark .sdw-schedule-cell {
        border-right-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-schedule-month {
        padding: 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        background-color: #f1f5f9;
        color: #475569;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .dark .sdw-schedule-month {
        background-color: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-schedule-spec {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        min-height: 3.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Registration Request List */
    .sdw-request-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .sdw-request-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background-color: #f8fafc;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .dark .sdw-request-item {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .sdw-request-num {
        font-weight: 700;
        color: #6b7280;
        min-width: 1.5rem;
    }

    .dark .sdw-request-num {
        color: #9ca3af;
    }

    .sdw-request-name {
        color: #374151;
        font-weight: 500;
    }

    .dark .sdw-request-name {
        color: #e5e7eb;
    }

    /* Assigned Facilities Table */
    .sdw-assigned-container {
        overflow-x: auto;
    }

    .sdw-assigned-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .sdw-assigned-table th,
    .sdw-assigned-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .sdw-assigned-table th,
    .dark .sdw-assigned-table td {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-assigned-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #374151;
    }

    .dark .sdw-assigned-table th {
        background-color: rgba(255, 255, 255, 0.05);
        color: #e5e7eb;
    }

    .sdw-assigned-table td {
        color: #4b5563;
    }

    .dark .sdw-assigned-table td {
        color: #d1d5db;
    }

    .sdw-assigned-table tr:last-child td {
        border-bottom: none;
    }

    .sdw-empty {
        color: #9ca3af;
        font-style: italic;
    }

    .sdw-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #ffffff;
        background-color: #7c3aed;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .sdw-action-btn:hover {
        background-color: #6d28d9;
    }

    .sdw-action-btn-secondary {
        background-color: #6b7280;
    }

    .sdw-action-btn-secondary:hover {
        background-color: #4b5563;
    }

    .sdw-section-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .sdw-divider {
        height: 1px;
        background-color: #e5e7eb;
        margin: 1rem 0;
    }

    .dark .sdw-divider {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .sdw-alert {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sdw-alert-info {
        background-color: #e0e7ff;
        color: #3730a3;
    }

    .dark .sdw-alert-info {
        background-color: rgba(55, 48, 163, 0.2);
        color: #a5b4fc;
    }

    .sdw-alert-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .dark .sdw-alert-success {
        background-color: rgba(21, 128, 61, 0.2);
        color: #4ade80;
    }
</style>
