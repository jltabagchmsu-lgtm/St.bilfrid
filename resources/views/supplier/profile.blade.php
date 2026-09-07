@extends('supplier.layout')

@section('title', 'Organization Settings - ' . $supplier->name)
@section('page_title', 'Supplier Profile & Settings')
@section('page_subtitle', 'Configure supplier contact information, logistics warehouse address, and account security credentials.')

@section('content')

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left: Profile and Contact Details Form -->
    <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: 14px; padding: 26px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-red-light); display: grid; place-items: center; color: var(--primary-red);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                Trade Organization Profile
            </h3>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 22px; margin-left: 42px;">
            This information is visible to the St. Bilfrid Dev. Corp procurement team when issuing purchase orders.
        </p>

        <form action="{{ route('supplier.profile.update') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Supplier Company Name
                    </label>
                    <input type="text" value="{{ $supplier->name }}" disabled class="input-field" style="width: 100%; cursor: not-allowed;">
                    <span style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 4px;">Trade name managed by Master Admin.</span>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Trade Category
                    </label>
                    <input type="text" value="{{ $supplier->category }}" disabled class="input-field" style="width: 100%; cursor: not-allowed;">
                    <span style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 4px;">Assigned procurement domain.</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Authorized Representative <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="e.g. Engr. Juan Dela Cruz" required class="input-field" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Direct Contact Number <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" placeholder="e.g. +63 (34) 495-8821" required class="input-field" style="width: 100%;">
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                    Registered Email Address
                </label>
                <input type="email" value="{{ $supplier->email }}" disabled class="input-field" style="width: 100%; cursor: not-allowed;">
                <span style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-top: 4px;">Primary account authentication email.</span>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                    Logistics / Plant Warehouse Address <span style="color: var(--primary-red);">*</span>
                </label>
                <textarea name="address" rows="3" placeholder="Enter physical warehouse location, staging yard, or pickup hub..." required class="input-field" style="width: 100%; resize: vertical;">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 22px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(245, 158, 11, 0.12); display: grid; place-items: center; color: #f59e0b;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                        Change Account Password
                    </h4>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 16px; margin-left: 36px;">
                    Leave fields blank if you do not wish to modify your current login password.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Current Password
                        </label>
                        <input type="password" name="current_password" placeholder="••••••••" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            New Password
                        </label>
                        <input type="password" name="new_password" placeholder="••••••••" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Confirm New Password
                        </label>
                        <input type="password" name="new_password_confirmation" placeholder="••••••••" class="input-field" style="width: 100%;">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 10px 24px; font-size: 0.85rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Save Organization Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Right: Account Overview & Performance Scorecard -->
    <div>
        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">
                Supplier Performance Scorecard
            </h3>

            <div style="text-align: center; padding: 20px 0; border-bottom: 1px solid var(--border-color);">
                <div style="font-size: 2.5rem; font-weight: 800; color: #059669; font-family: var(--font-mono); line-height: 1;">
                    {{ number_format($supplier->rating, 2) }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;">
                    Out of 5.00 Quality Rating
                </div>
                <div style="margin-top: 10px;">
                    <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);">
                        Accredited Grade A Partner
                    </span>
                </div>
            </div>

            <div style="margin-top: 16px; font-size: 0.825rem; color: var(--text-secondary);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Supplier Code:</span>
                    <strong style="font-family: var(--font-mono); color: var(--primary-red);">{{ $supplier->code }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Trade Status:</span>
                    <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                        {{ ucfirst($supplier->status) }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-muted);">Materials Cataloged:</span>
                    <strong style="color: var(--text-primary); font-family: var(--font-mono);">{{ $supplier->materials()->count() }} items</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span style="color: var(--text-muted);">Total Orders Received:</span>
                    <strong style="color: var(--text-primary); font-family: var(--font-mono);">{{ $supplier->orders()->count() }} orders</strong>
                </div>
            </div>
        </div>

        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary);">
                    Contractor Assistance
                </h4>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.5;">
                For technical discrepancies or master catalog modifications, please coordinate directly with the St. Bilfrid Dev. Corp Engineering & Procurement Office.
            </p>
        </div>
    </div>
</div>

@endsection
