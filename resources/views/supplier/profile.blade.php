@extends('supplier.layout')

@section('title', 'Organization Settings - ' . $supplier->name)
@section('page_title', 'Supplier Profile & Settings')
@section('page_subtitle', 'Configure supplier contact information, logistics warehouse address, and account security credentials.')

@section('content')

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left: Profile and Contact Details Form -->
    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 24px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">
            Trade Organization Profile
        </h3>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px;">
            This information is visible to the St. Bilfrid Dev. Corp procurement team when issuing purchase orders.
        </p>

        <form action="{{ route('supplier.profile.update') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Supplier Company Name
                    </label>
                    <input type="text" value="{{ $supplier->name }}" disabled class="input-field" style="width: 100%; opacity: 0.7; cursor: not-allowed;">
                    <span style="font-size: 0.7rem; color: var(--text-muted);">Trade name managed by Master Admin.</span>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Trade Category
                    </label>
                    <input type="text" value="{{ $supplier->category }}" disabled class="input-field" style="width: 100%; opacity: 0.7; cursor: not-allowed;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Authorized Representative <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" required class="input-field" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Direct Contact Number <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required class="input-field" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                    Registered Email Address
                </label>
                <input type="email" value="{{ $supplier->email }}" disabled class="input-field" style="width: 100%; opacity: 0.7; cursor: not-allowed;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                    Logistics / Plant Warehouse Address <span style="color: var(--primary-red);">*</span>
                </label>
                <textarea name="address" rows="3" required class="input-field" style="width: 100%; resize: vertical;">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 20px; margin-bottom: 20px;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">
                    Change Account Password
                </h4>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 16px;">
                    Leave blank if you do not wish to modify your login password.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Current Password
                        </label>
                        <input type="password" name="current_password" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            New Password
                        </label>
                        <input type="password" name="new_password" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Confirm New Password
                        </label>
                        <input type="password" name="new_password_confirmation" class="input-field" style="width: 100%;">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 10px 24px; font-size: 0.85rem;">
                    Save Organization Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Right: Account Overview & Performance Scorecard -->
    <div>
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">
                Supplier Performance Scorecard
            </h3>

            <div style="text-align: center; padding: 18px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                <div style="font-size: 2.25rem; font-weight: 800; color: #10b981; font-family: var(--font-mono);">
                    {{ number_format($supplier->rating, 2) }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    Out of 5.00 Quality Rating
                </div>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; margin-top: 8px;">
                    Accredited Grade A Partner
                </span>
            </div>

            <div style="margin-top: 16px; font-size: 0.8rem; color: var(--text-secondary);">
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span>Supplier Code:</span>
                    <strong style="font-family: var(--font-mono); color: #38bdf8;">{{ $supplier->code }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span>Trade Status:</span>
                    <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        {{ ucfirst($supplier->status) }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span>Materials Cataloged:</span>
                    <strong style="color: var(--text-primary);">{{ $supplier->materials()->count() }} items</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span>Total Orders Received:</span>
                    <strong style="color: var(--text-primary);">{{ $supplier->orders()->count() }} orders</strong>
                </div>
            </div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px;">
            <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                Contractor Assistance
            </h4>
            <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                For technical discrepancies or master catalog modifications, please coordinate directly with the St. Bilfrid Dev. Corp Engineering & Procurement Office.
            </p>
        </div>
    </div>
</div>

@endsection
