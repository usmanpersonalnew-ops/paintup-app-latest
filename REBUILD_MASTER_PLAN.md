# SUPERVISOR MODULE V4: MASTER REBUILD SPECIFICATION

## 1. PROJECT OBJECTIVE
**Goal:** A complete rebuild of the Supervisor Module to fix "White Screens" and "Missing Fields".
**Source of Truth:** This document contains the exact Database Schema, Routes, and UI Wireframes required.
**Constraint:** You must follow the Wireframes pixel-for-pixel.

---

## 2. DATABASE SCHEMA AUDIT (CRITICAL)
**Action:** Verify these tables exist. If any column is missing, create a migration immediately.

### A. Table: `project_zones` (The Rooms)
- `id` (PK)
- `project_id` (FK)
- `name` (String) — *e.g., "Master Bedroom"*
- `type` (Enum: 'Interior', 'Exterior')
- `length`, `breadth`, `height` (Decimal, Nullable) — *Default dimensions used in Screen C*

### B. Table: `quote_items` (Paint Jobs)
- `id` (PK)
- `project_zone_id` (FK) — *Links to the Room*
- `surface_id`, `product_id`, `system_id` (FKs)
- `measurement_mode` (Enum: 'DEFAULT', 'MANUAL')
- `manual_length`, `manual_breadth` (Decimal, Nullable)
- **`deductions`** (Decimal, Default 0) — *Required for Net Area logic*
- **`pricing_mode`** (Enum: 'CALCULATED', 'LUMPSUM')
- `manual_price` (Decimal, Nullable)
- `calculated_qty`, `final_amount` (Decimal)

### C. Table: `quote_services` (Services & Repairs)
- `id` (PK)
- `project_zone_id` (FK)
- `master_service_id` (FK, Nullable for Custom)
- `custom_name` (String)
- **`unit_type`** (Enum: 'AREA', 'LINEAR', 'COUNT') — *Drivers the Dynamic UI*
- `length`, `breadth`, `count` (Decimal, Nullable)
- `rate`, `final_amount` (Decimal)
- **`photo`** (String, Nullable) — *Mandatory for Repairs*

---

## 3. ROUTES (`routes/web.php`)
**Action:** Replace the existing supervisor routes with this group.

```php
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    // Screen A: Create Zone
    Route::post('/projects/{project}/zones', [ZoneController::class, 'store'])->name('zones.store');

    // Screen B: Dashboard (The Hub)
    Route::get('/zones/{zone}', [ZoneDashboardController::class, 'index'])->name('zones.show');

    // Screen C: Paint Calculator
    Route::get('/zones/{zone}/paint', [QuoteItemController::class, 'create'])->name('zones.paint.create');
    Route::post('/zones/{zone}/paint', [QuoteItemController::class, 'store'])->name('zones.paint.store');

    // Screen D: Service/Repair Module
    Route::get('/zones/{zone}/service', [ServiceController::class, 'create'])->name('zones.service.create');
    Route::post('/zones/{zone}/service', [ServiceController::class, 'store'])->name('zones.service.store');
    
    // Screen E: Summary
    Route::get('/projects/{project}/summary', [SummaryController::class, 'show'])->name('summary');
});


4. UI WIREFRAMES & LOGIC (SCREENS A-E)
SCREEN A: CREATE ZONE (Supervisor/Zones/Create.vue)
Logic: Capture default dimensions to save time later.

Plaintext
[ Form Layout ]
1. Name: [ Input ]
2. Type: (•) Interior  ( ) Exterior
3. Default Dimensions (Optional):
   L: [   ]   B: [   ]   H: [   ]
[ CREATE ZONE ]
SCREEN B: ZONE DASHBOARD (Supervisor/Zones/Dashboard.vue)
Critical Fix: Ensure Controller uses $zone->load(['items', 'services']) to prevent White Screen.

Layout:

Plaintext
[ Header: Zone Name | 12x10x10 ft ]
[ Section 1: Photos (Grid) ]
[ Section 2: Items List ]
  - Wall (Royale Glitz) ... ₹ 25,000
  - Crack Filling ......... ₹    500
[ Buttons ]
  [ + ADD PAINT ITEM ]  -> Links to Screen C
  [ + ADD SERVICE    ]  -> Links to Screen D
SCREEN C: ADD PAINT ITEM (Supervisor/Paint/Create.vue)
Reference: V4 PDF Page 5.

Requirements:

Product Filter: Add Toggles [All] [Eco] [Prem] [Lux] above the Product Dropdown.

Measurement Logic:

Mode: [Default] vs [Manual].

Default: Auto-fill Gross Area = Zone L * Zone H.

Manual: Show Length and Height inputs.

Deductions: Add Input Deductions (sqft).

Formula: Net Qty = Gross Area - Deductions.

Pricing: [Calculated] vs [Lumpsum].

SCREEN D: ADD SERVICE / REPAIR (Supervisor/Service/Create.vue)
Reference: V4 PDF Page 6.

Requirements:

Mode Toggle: [Catalog] vs [Custom].

Dynamic Inputs (Watch unit_type):

AREA: Show Length & Height. Qty = L * H.

LINEAR: Show Length. Qty = L.

COUNT: Show Quantity.

Repair Evidence: If service.is_repair == 1, show <input type="file" required>.

Summary: Show Qty x Rate = Total.

SCREEN E: SUMMARY (Supervisor/Summary/Show.vue)
Layout: List all Zones with subtotals.

Footer:

Global Discount: Input field (Flat Amount).

Tax: Display 18% GST.

Grand Total: (Subtotal - Discount) + Tax.

5. EXECUTION PLAN
Stop: Do not guess. Read this entire file first.

Database: Run php artisan make:migration add_columns_to_quote_tables if columns from Section 2 are missing.

Backend: Fix ZoneDashboardController to eager load relationships (fixes White Screen).

Frontend: Delete existing Create.vue files and rebuild them from scratch using the Wireframes in Section 4.