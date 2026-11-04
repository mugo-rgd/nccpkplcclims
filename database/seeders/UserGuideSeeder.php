<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuideCategory;
use App\Models\UserGuide;
use App\Models\User;

class UserGuideSeeder extends Seeder
{
    public function run()
    {
        // Get admin user
        $admin = User::first();

        // Create Categories
$categories = [
    ['name' => 'Getting Started', 'slug' => 'getting-started', 'icon' => 'rocket', 'order' => 1],
    ['name' => 'Customer Management', 'slug' => 'customers', 'icon' => 'users', 'order' => 2],
    ['name' => 'Calibrations', 'slug' => 'calibrations', 'icon' => 'microscope', 'order' => 3],
    ['name' => 'Test Management', 'slug' => 'test-management', 'icon' => 'flask', 'order' => 4],
    ['name' => 'Quality Control', 'slug' => 'quality-control', 'icon' => 'clipboard-check', 'order' => 5],
    ['name' => 'Certificates', 'slug' => 'certificates', 'icon' => 'certificate', 'order' => 6],
    ['name' => 'Equipment', 'slug' => 'equipment', 'icon' => 'tools', 'order' => 7],
    ['name' => 'Reports', 'slug' => 'reports', 'icon' => 'chart-bar', 'order' => 8],
    ['name' => 'FAQ', 'slug' => 'faq', 'icon' => 'question-circle', 'order' => 9],
];
        foreach ($categories as $category) {
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // ============================================
        // GETTING STARTED GUIDES
        // ============================================
        $gettingStartedGuides = [
            [
                'title' => 'System Overview',
                'slug' => 'system-overview',
                'category' => 'getting-started',
                'order' => 1,
                'icon' => 'info-circle',
                'content' => $this->getSystemOverview()
            ],
            [
                'title' => 'How to Login',
                'slug' => 'how-to-login',
                'category' => 'getting-started',
                'order' => 2,
                'icon' => 'sign-in-alt',
                'content' => $this->getLoginGuide()
            ],
            [
                'title' => 'Dashboard Overview',
                'slug' => 'dashboard-overview',
                'category' => 'getting-started',
                'order' => 3,
                'icon' => 'tachometer-alt',
                'content' => $this->getDashboardGuide()
            ],
        ];

        foreach ($gettingStartedGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // CUSTOMER MANAGEMENT GUIDES
        // ============================================
        $customerGuides = [
            [
                'title' => 'Creating a New Customer',
                'slug' => 'creating-customer',
                'category' => 'customers',
                'order' => 1,
                'icon' => 'user-plus',
                'content' => $this->getCustomerGuide()
            ],
            [
                'title' => 'Managing Customers',
                'slug' => 'managing-customers',
                'category' => 'customers',
                'order' => 2,
                'icon' => 'users-cog',
                'content' => $this->getManagingCustomersGuide()
            ],
        ];

        foreach ($customerGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // CALIBRATIONS GUIDES
        // ============================================
        $calibrationGuides = [
            [
                'title' => 'Creating a Calibration Request',
                'slug' => 'creating-calibration',
                'category' => 'calibrations',
                'order' => 1,
                'icon' => 'plus-circle',
                'content' => $this->getCalibrationGuide()
            ],
            [
                'title' => 'Assigning a Technician',
                'slug' => 'assigning-technician',
                'category' => 'calibrations',
                'order' => 2,
                'icon' => 'user-check',
                'content' => $this->getAssignTechnicianGuide()
            ],
            [
                'title' => 'Calibration Status Flow',
                'slug' => 'calibration-status-flow',
                'category' => 'calibrations',
                'order' => 3,
                'icon' => 'project-diagram',
                'content' => $this->getStatusFlowGuide()
            ],
        ];

        foreach ($calibrationGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // TEST MANAGEMENT GUIDES
        // ============================================
        $testManagementGuides = [
            [
                'title' => 'Adding Test Results',
                'slug' => 'adding-test-results',
                'category' => 'test-management',
                'order' => 1,
                'icon' => 'flask',
                'content' => $this->getAddTestResultsGuide()
            ],
            [
                'title' => 'Understanding Uncertainty Calculation',
                'slug' => 'uncertainty-calculation',
                'category' => 'test-management',
                'order' => 2,
                'icon' => 'calculator',
                'content' => $this->getUncertaintyGuide()
            ],
            [
                'title' => 'Tolerance and PASS/FAIL Determination',
                'slug' => 'tolerance-pass-fail',
                'category' => 'test-management',
                'order' => 3,
                'icon' => 'balance-scale',
                'content' => $this->getToleranceGuide()
            ],
            [
                'title' => 'Viewing and Managing Test Results',
                'slug' => 'managing-test-results',
                'category' => 'test-management',
                'order' => 4,
                'icon' => 'list-ul',
                'content' => $this->getManagingTestResultsGuide()
            ],
        ];

        foreach ($testManagementGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
// QUALITY CONTROL GUIDES
// ============================================
$qualityControlGuides = [
    [
        'title' => 'Quality Controller Dashboard',
        'slug' => 'quality-controller-dashboard',
        'category' => 'quality-control',
        'order' => 1,
        'icon' => 'tachometer-alt',
        'content' => $this->getQualityDashboardGuide()
    ],
    [
        'title' => 'Quality Check Process',
        'slug' => 'quality-check-process',
        'category' => 'quality-control',
        'order' => 2,
        'icon' => 'clipboard-check',
        'content' => $this->getQualityCheckProcessGuide()
    ],
    [
        'title' => 'Quality Decision Guide',
        'slug' => 'quality-decision-guide',
        'category' => 'quality-control',
        'order' => 3,
        'icon' => 'gavel',
        'content' => $this->getQualityDecisionGuide()
    ],
    [
        'title' => 'Quality Standards',
        'slug' => 'quality-standards',
        'category' => 'quality-control',
        'order' => 4,
        'icon' => 'book',
        'content' => $this->getQualityStandardsGuide()
    ],
    [
        'title' => 'Quality Reports',
        'slug' => 'quality-reports',
        'category' => 'quality-control',
        'order' => 5,
        'icon' => 'chart-bar',
        'content' => $this->getQualityReportsGuide()
    ],
];

foreach ($qualityControlGuides as $guide) {
    UserGuide::updateOrCreate(
        ['slug' => $guide['slug']],
        array_merge($guide, [
            'content_html' => $guide['content'],
            'is_published' => true,
            'created_by' => $admin->id ?? 1
        ])
    );
}

        // ============================================
        // CERTIFICATES GUIDES
        // ============================================
        $certificateGuides = [
            [
                'title' => 'Generating Certificates',
                'slug' => 'generating-certificates',
                'category' => 'certificates',
                'order' => 1,
                'icon' => 'file-pdf',
                'content' => $this->getCertificateGuide()
            ],
            [
                'title' => 'Certificate Workflow',
                'slug' => 'certificate-workflow',
                'category' => 'certificates',
                'order' => 2,
                'icon' => 'flowchart',
                'content' => $this->getCertificateWorkflowGuide()
            ],
        ];

        foreach ($certificateGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // EQUIPMENT GUIDES
        // ============================================
        $equipmentGuides = [
            [
                'title' => 'Managing Equipment Inventory',
                'slug' => 'managing-equipment',
                'category' => 'equipment',
                'order' => 1,
                'icon' => 'tools',
                'content' => $this->getEquipmentGuide()
            ],
        ];

        foreach ($equipmentGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // REPORTS GUIDES
        // ============================================
        $reportGuides = [
            [
                'title' => 'Generating Reports',
                'slug' => 'generating-reports',
                'category' => 'reports',
                'order' => 1,
                'icon' => 'chart-bar',
                'content' => $this->getReportsGuide()
            ],
        ];

        foreach ($reportGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }

        // ============================================
        // FAQ GUIDES
        // ============================================
        $faqGuides = [
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'category' => 'faq',
                'order' => 1,
                'icon' => 'question',
                'content' => $this->getFAQ()
            ],
        ];

        foreach ($faqGuides as $guide) {
            UserGuide::updateOrCreate(
                ['slug' => $guide['slug']],
                array_merge($guide, [
                    'content_html' => $guide['content'],
                    'is_published' => true,
                    'created_by' => $admin->id ?? 1
                ])
            );
        }
    }

    // ============================================
    // CONTENT METHODS
    // ============================================

    private function getSystemOverview()
    {
        return '<h2>Welcome to KPLC MCLab System</h2>
        <p>The Meter Central Laboratory System is a comprehensive web-based application designed to manage the entire calibration lifecycle.</p>

        <h3>Key Features</h3>
        <ul>
            <li><strong>Customer Management</strong> - Create, edit, and manage customer records</li>
            <li><strong>Calibration Workflow</strong> - Full lifecycle from request to certificate</li>
            <li><strong>Test Management</strong> - Record test results with uncertainty calculations</li>
            <li><strong>Role-Based Access</strong> - Different permissions for different user roles</li>
            <li><strong>Digital Signatures</strong> - Electronic signatures for certificates</li>
            <li><strong>Document Generation</strong> - PDF generation for all official documents</li>
            <li><strong>Equipment Tracking</strong> - Manage laboratory equipment inventory</li>
            <li><strong>Audit Trail</strong> - Track all system activities</li>
        </ul>

        <h3>System Roles</h3>
        <table>
            <thead><tr><th>Role</th><th>Responsibilities</th></tr></thead>
            <tbody>
                <tr><td><strong>Super Admin</strong></td><td>System configuration, user management, overall oversight</td></tr>
                <tr><td><strong>Lab Manager</strong></td><td>Approve certificates, oversee operations, manage staff</td></tr>
                <tr><td><strong>Supervisor</strong></td><td>Check certificates, verify test results, quality assurance</td></tr>
                <tr><td><strong>Lab Technician</strong></td><td>Perform calibrations, record test results, generate certificates</td></tr>
                <tr><td><strong>Customer Service</strong></td><td>Create customer accounts, manage requests, handle inquiries</td></tr>
                <tr><td><strong>Customer</strong></td><td>Submit requests, view certificates, track calibration status</td></tr>
            </tbody>
        </table>';
    }

    private function getLoginGuide()
    {
        return '<h2>How to Login to the System</h2>

        <h3>Step 1: Open Your Browser</h3>
        <p>Open Google Chrome (recommended), Mozilla Firefox, or Microsoft Edge.</p>

        <h3>Step 2: Navigate to the System</h3>
        <p>Enter the system URL in your browser\'s address bar.</p>

        <h3>Step 3: Enter Your Credentials</h3>
        <p>Enter your registered email and password.</p>

        <h3>Default Login Credentials</h3>
        <table>
            <thead><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead>
            <tbody>
                <tr><td>Super Admin</td><td>admin@kplc.co.ke</td><td>Admin@123</td></tr>
                <tr><td>Lab Manager</td><td>labmanager@kplc.co.ke</td><td>Manager@123</td></tr>
                <tr><td>Supervisor</td><td>supervisor@kplc.co.ke</td><td>Supervisor@123</td></tr>
                <tr><td>Technician</td><td>tech@kplc.co.ke</td><td>Tech@123</td></tr>
            </tbody>
        </table>

        <div class="alert alert-warning">
            <strong>⚠️ Important:</strong> Change your password immediately after first login!
        </div>';
    }

    private function getDashboardGuide()
    {
        return '<h2>Dashboard Overview</h2>
        <p>The Dashboard provides a quick overview of system statistics and activities.</p>

        <h3>Dashboard Components</h3>
        <ul>
            <li><strong>Statistics Cards</strong> - Quick overview of calibration counts</li>
            <li><strong>Recent Activities</strong> - Latest calibrations and updates</li>
            <li><strong>Equipment Gallery</strong> - Display of laboratory equipment</li>
            <li><strong>Quick Actions</strong> - Create new calibration, add customer</li>
        </ul>';
    }

    private function getCustomerGuide()
    {
        return '<h2>Creating a New Customer</h2>

        <h3>Navigation</h3>
        <p>Main Menu → Customers → Add Customer</p>

        <h3>Step 1: Fill Customer Details</h3>
        <table>
            <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Company Name</td><td>✅ Yes</td><td>Full company name</td></tr>
                <tr><td>Contact Person</td><td>✅ Yes</td><td>Primary contact name</td></tr>
                <tr><td>Phone</td><td>✅ Yes</td><td>Contact phone number</td></tr>
                <tr><td>Email</td><td>❌ No</td><td>Customer email (for notifications)</td></tr>
                <tr><td>Address</td><td>✅ Yes</td><td>Physical address</td></tr>
                <tr><td>Tax ID (PIN)</td><td>❌ No</td><td>KRA PIN number</td></tr>
            </tbody>
        </table>

        <h3>Step 2: Click "Create Customer"</h3>
        <p>Review all information and click the <strong>Create Customer</strong> button.</p>';
    }

    private function getManagingCustomersGuide()
    {
        return '<h2>Managing Customers</h2>

        <h3>Viewing Customers</h3>
        <p>Main Menu → Customers</p>

        <h3>Editing a Customer</h3>
        <ol>
            <li>Navigate to <strong>Customers</strong></li>
            <li>Click the <strong>Edit (✏️)</strong> button next to the customer</li>
            <li>Update the required fields</li>
            <li>Click <strong>Update Customer</strong></li>
        </ol>

        <h3>Viewing Customer Details</h3>
        <p>Click on a customer row to view:</p>
        <ul>
            <li>Company information</li>
            <li>Contact details</li>
            <li>Calibration history</li>
            <li>Equipment list</li>
            <li>Certificate history</li>
        </ul>';
    }

    private function getCalibrationGuide()
    {
        return '<h2>Creating a Calibration Request</h2>

        <h3>Navigation</h3>
        <p>Main Menu → Calibrations → New Calibration</p>

        <h3>Step 1: Select Customer</h3>
        <ul>
            <li>Choose the customer from the dropdown list</li>
            <li>If customer doesn\'t exist, create one first</li>
        </ul>

        <h3>Step 2: Enter Equipment Details</h3>
        <table>
            <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Equipment Name</td><td>✅ Yes</td><td>Name of equipment</td></tr>
                <tr><td>Serial Number</td><td>✅ Yes</td><td>Unique serial number</td></tr>
                <tr><td>Manufacturer</td><td>❌ No</td><td>Equipment manufacturer</td></tr>
                <tr><td>Model</td><td>❌ No</td><td>Equipment model</td></tr>
                <tr><td>Condition</td><td>✅ Yes</td><td>Excellent/Good/Fair/Poor/Damaged</td></tr>
            </tbody>
        </table>

        <h3>Step 3: Click "Create Calibration"</h3>
        <p>Review all information and click the <strong>Create Calibration</strong> button.</p>';
    }

    private function getAssignTechnicianGuide()
    {
        return '<h2>Assigning a Technician</h2>

        <h3>Navigation</h3>
        <p>Open the calibration details → Click Assign Technician</p>

        <h3>Steps</h3>
        <ol>
            <li>Open the calibration details</li>
            <li>Click <strong>Assign Technician</strong></li>
            <li>Select a technician from the dropdown</li>
            <li>Add assignment notes (optional)</li>
            <li>Click <strong>Assign Technician</strong></li>
        </ol>

        <div class="alert alert-info">
            <strong>Note:</strong> Only technicians with the appropriate role will appear in the dropdown.
        </div>';
    }

    private function getStatusFlowGuide()
    {
        return '<h2>Calibration Status Flow</h2>

        <h3>External Customers</h3>
        <div class="bg-light p-3 rounded">
            <p class="text-center">
                Pending → Quoted → Paid → Assigned → In Progress → Testing → Completed → Certified
            </p>
        </div>

        <h3>Internal (KPLC) Customers</h3>
        <div class="bg-light p-3 rounded">
            <p class="text-center">
                Pending → Assigned → In Progress → Testing → Completed → Certified
            </p>
        </div>

        <h3>Status Descriptions</h3>
        <table>
            <thead><tr><th>Status</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Pending</td><td>Calibration request submitted</td></tr>
                <tr><td>Quoted</td><td>Quote sent to customer (External)</td></tr>
                <tr><td>Paid</td><td>Payment received (External)</td></tr>
                <tr><td>Assigned</td><td>Technician assigned</td></tr>
                <tr><td>In Progress</td><td>Work started</td></tr>
                <tr><td>Testing</td><td>Testing in progress</td></tr>
                <tr><td>Completed</td><td>Testing complete, ready for certificate</td></tr>
                <tr><td>Certified</td><td>Certificate generated and sent</td></tr>
            </tbody>
        </table>';
    }

    private function getAddTestResultsGuide()
    {
        return '<h2>Adding Test Results</h2>

        <h3>Prerequisites</h3>
        <ul>
            <li>You must be the <strong>assigned technician</strong></li>
            <li>Calibration must be in <strong>"Assigned"</strong> or <strong>"In Progress"</strong> status</li>
        </ul>

        <h3>Navigation</h3>
        <p>Calibrations → [Select Calibration] → Tests → Add Test Result</p>

        <h3>Step 1: Enter Test Information</h3>
        <table>
            <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Test Name</td><td>✅ Yes</td><td>E.g., "Accuracy at 0.05 Ib, 1.0PF"</td></tr>
                <tr><td>Test Type</td><td>✅ Yes</td><td>Accuracy, Register, Starting Current, Creep</td></tr>
                <tr><td>Applied Value</td><td>❌ No</td><td>E.g., "0.05Ib(0.5A);1PF;230V;50HZ"</td></tr>
                <tr><td>Power Factor</td><td>❌ No</td><td>1.0PF, 0.5LAG, 0.5LEAD</td></tr>
                <tr><td>Load Type</td><td>❌ No</td><td>Low Load, Base Load, Inductive Load, Maximum Load</td></tr>
                <tr><td>Voltage</td><td>❌ No</td><td>E.g., "230V"</td></tr>
                <tr><td>Frequency</td><td>❌ No</td><td>E.g., "50HZ"</td></tr>
            </tbody>
        </table>

        <h3>Step 2: Enter Test Results</h3>
        <ul>
            <li><strong>Tolerance:</strong> Default 0.5%, adjustable up to 1%</li>
            <li><strong>Trial Data:</strong> Enter 10 readings for uncertainty calculation</li>
            <li>Click <strong>Calculate Uncertainty</strong> to auto-calculate values</li>
        </ul>

        <h3>Step 3: Save</h3>
        <p>Click <strong>Save Test Result</strong> to save the test.</p>

        <div class="alert alert-success">
            <strong>✅ Tip:</strong> The system will auto-set PASS/FAIL based on the tolerance and error value.
        </div>';
    }

    private function getUncertaintyGuide()
    {
        return '<h2>Understanding Uncertainty Calculation</h2>

        <h3>What is Measurement Uncertainty?</h3>
        <p>Measurement uncertainty quantifies the doubt about the result of a measurement. It indicates the range of values within which the true value is expected to lie with a certain confidence level.</p>

        <h3>Uncertainty Components</h3>
        <table>
            <thead><tr><th>Component</th><th>Description</th><th>Formula</th></tr></thead>
            <tbody>
                <tr><td><strong>u₁</strong></td><td>Repeatability (Type A)</td><td>s / √n</td></tr>
                <tr><td><strong>u₂</strong></td><td>Reference Standard (Type B)</td><td>RefStd / 2</td></tr>
                <tr><td><strong>u₃</strong></td><td>UUT Resolution (Type B)</td><td>(Resolution / 2) / √3</td></tr>
            </tbody>
        </table>

        <h3>Reference Standard Values</h3>
        <table>
            <thead><tr><th>Load Type</th><th>Reference Standard Value</th></tr></thead>
            <tbody>
                <tr><td>Low Load (0.05 Ib)</td><td>0.0769%</td></tr>
                <tr><td>Base Load (Ib)</td><td>0.1041%</td></tr>
                <tr><td>Inductive Load (Ib)</td><td>0.0768%</td></tr>
                <tr><td>Maximum Load (IMAX)</td><td>0.1178%</td></tr>
            </tbody>
        </table>

        <h3>Formulas</h3>
        <div class="bg-light p-3 rounded">
            <p><strong>Combined Uncertainty:</strong> uc = √(u₁² + u₂² + u₃²)</p>
            <p><strong>Expanded Uncertainty:</strong> U = uc × 2 (95% confidence level)</p>
        </div>

        <h3>Example Calculation</h3>
        <p><strong>Trial Data:</strong> 0.20, 0.21, 0.22, 0.21, 0.20, 0.22, 0.21, 0.20, 0.21, 0.22</p>
        <ul>
            <li><strong>Average Error:</strong> 0.2090%</li>
            <li><strong>u₁:</strong> 0.0026%</li>
            <li><strong>u₂:</strong> 0.0521% (Base Load)</li>
            <li><strong>u₃:</strong> 0.0289%</li>
            <li><strong>Combined (uc):</strong> 0.0482%</li>
            <li><strong>Expanded (U):</strong> 0.0964%</li>
        </ul>';
    }

    private function getToleranceGuide()
    {
        return '<h2>Tolerance and PASS/FAIL Determination</h2>

        <h3>Tolerance Settings</h3>
        <ul>
            <li><strong>Default:</strong> 0.5%</li>
            <li><strong>Minimum:</strong> 0.1%</li>
            <li><strong>Maximum:</strong> 1.0%</li>
        </ul>

        <h3>How PASS/FAIL is Determined</h3>
        <div class="bg-light p-3 rounded">
            <p><strong>PASS:</strong> |Error| ≤ Tolerance</p>
            <p><strong>FAIL:</strong> |Error| > Tolerance</p>
        </div>

        <h3>Examples</h3>
        <table>
            <thead><tr><th>Error Value</th><th>Tolerance</th><th>Result</th></tr></thead>
            <tbody>
                <tr><td>0.209%</td><td>0.5%</td><td><span class="badge bg-success">PASS</span></td></tr>
                <tr><td>0.450%</td><td>0.5%</td><td><span class="badge bg-success">PASS</span></td></tr>
                <tr><td>0.750%</td><td>0.5%</td><td><span class="badge bg-danger">FAIL</span></td></tr>
                <tr><td>0.209%</td><td>0.2%</td><td><span class="badge bg-danger">FAIL</span></td></tr>
                <tr><td>0.150%</td><td>0.2%</td><td><span class="badge bg-success">PASS</span></td></tr>
            </tbody>
        </table>

        <div class="alert alert-info">
            <strong>💡 Tip:</strong> The result is auto-set when you click "Calculate Uncertainty".
        </div>';
    }

    private function getManagingTestResultsGuide()
    {
        return '<h2>Viewing and Managing Test Results</h2>

        <h3>Viewing Test Results</h3>
        <p>Navigation: Calibrations → [Select Calibration] → Tests → View All</p>

        <h3>Test Results Table</h3>
        <table>
            <thead><tr><th>Column</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Test Information</td><td>Test name, type, power factor, voltage, frequency</td></tr>
                <tr><td>Applied Value</td><td>Test conditions applied</td></tr>
                <tr><td>Error %</td><td>Average error from trial data</td></tr>
                <tr><td>Uncertainty</td><td>Expanded uncertainty (U)</td></tr>
                <tr><td>Result</td><td>PASS or FAIL</td></tr>
                <tr><td>Actions</td><td>View, Edit, Delete</td></tr>
            </tbody>
        </table>

        <h3>Viewing Test Details</h3>
        <ol>
            <li>Click the <strong>View (👁️)</strong> button</li>
            <li>A modal will show:
                <ul>
                    <li>Complete test information</li>
                    <li>Uncertainty components (u₁, u₂, u₃, combined, expanded)</li>
                    <li>Trial data (10 readings)</li>
                    <li>Register test details (if applicable)</li>
                    <li>Notes and metadata</li>
                </ul>
            </li>
        </ol>

        <h3>Editing a Test Result</h3>
        <ol>
            <li>Click the <strong>Edit (✏️)</strong> button</li>
            <li>Update the required fields</li>
            <li>Click <strong>Update Test Result</strong></li>
        </ol>

        <h3>Deleting a Test Result</h3>
        <ol>
            <li>Click the <strong>Delete (🗑️)</strong> button</li>
            <li>Confirm deletion</li>
        </ol>

        <div class="alert alert-warning">
            <strong>⚠️ Warning:</strong> Deleting a test result also deletes its uncertainty calculation. This action cannot be undone.
        </div>';
    }

    private function getCertificateGuide()
    {
        return '<h2>Generating Certificates</h2>

        <h3>Prerequisites</h3>
        <ul>
            <li>Calibration must be in <strong>"Completed"</strong> status</li>
            <li>All test results must be recorded</li>
            <li>Test results must have uncertainty calculations</li>
        </ul>

        <h3>Steps</h3>
        <ol>
            <li>Open the calibration details</li>
            <li>Click <strong>Generate Certificate</strong></li>
            <li>The certificate is created automatically with:
                <ul>
                    <li>Test results</li>
                    <li>Uncertainty calculations</li>
                    <li>Environmental conditions</li>
                    <li>Traceability information</li>
                </ul>
            </li>
            <li>You will be redirected to the certificate view</li>
        </ol>';
    }

    private function getCertificateWorkflowGuide()
    {
        return '<h2>Certificate Workflow</h2>

        <div class="bg-light p-3 rounded text-center">
            <p>Technician Generates Certificate</p>
            <p>↓</p>
            <p>Upload Test Report (from machine)</p>
            <p>↓</p>
            <p>Supervisor Checks & Signs</p>
            <p>↓</p>
            <p>Lab Manager Approves & Signs</p>
            <p>↓</p>
            <p>Send to Customer</p>
        </div>

        <h3>Certificate Status</h3>
        <table>
            <thead><tr><th>Status</th><th>Description</th><th>Action</th></tr></thead>
            <tbody>
                <tr><td>Generated</td><td>Certificate created</td><td>Supervisor to check</td></tr>
                <tr><td>Checked</td><td>Supervisor reviewed</td><td>Lab Manager to approve</td></tr>
                <tr><td>Approved</td><td>Lab Manager approved</td><td>Send to customer</td></tr>
                <tr><td>Sent</td><td>Sent to customer</td><td>Complete</td></tr>
            </tbody>
        </table>';
    }

    private function getEquipmentGuide()
    {
        return '<h2>Managing Equipment Inventory</h2>

        <h3>Viewing Equipment</h3>
        <p>Main Menu → Equipment</p>

        <h3>Adding Equipment</h3>
        <ol>
            <li>Navigate to Equipment</li>
            <li>Click <strong>Add Equipment</strong></li>
            <li>Fill in equipment details:
                <ul>
                    <li>Name, Serial Number, Model</li>
                    <li>Manufacturer, Category</li>
                    <li>Status, Condition</li>
                </ul>
            </li>
            <li>Click <strong>Save Equipment</strong></li>
        </ol>

        <h3>Equipment Status</h3>
        <table>
            <thead><tr><th>Status</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Active</td><td>Available for use</td></tr>
                <tr><td>In Use</td><td>Currently deployed</td></tr>
                <tr><td>Under Repair</td><td>Being repaired</td></tr>
                <tr><td>Calibration Due</td><td>Needs calibration</td></tr>
                <tr><td>Retired</td><td>No longer in service</td></tr>
            </tbody>
        </table>';
    }

    private function getReportsGuide()
    {
        return '<h2>Generating Reports</h2>

        <h3>Available Reports</h3>
        <ul>
            <li><strong>Calibrations Report</strong> - View and export calibration data</li>
            <li><strong>Customers Report</strong> - Customer activity and calibration statistics</li>
            <li><strong>Certificates Report</strong> - Certificate generation and status</li>
            <li><strong>Equipment Report</strong> - Laboratory equipment inventory</li>
        </ul>

        <h3>Steps to Generate a Report</h3>
        <ol>
            <li>Navigate to <strong>Reports</strong></li>
            <li>Select the report type</li>
            <li>Apply filters (date range, status, etc.)</li>
            <li>Click <strong>Generate Report</strong></li>
            <li>Export to PDF or Excel if needed</li>
        </ol>';
    }

    private function getFAQ()
    {
        return '<h2>Frequently Asked Questions</h2>

        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How do I reset my password?
                    </button>
                </h3>
                <div id="faq1" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        Click "Forgot Password" on the login page and follow the instructions.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Why can\'t I add test results?
                    </button>
                </h3>
                <div id="faq2" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        You must be the assigned technician and the calibration must be in "Assigned" or "In Progress" status.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        How many trial readings are required?
                    </button>
                </h3>
                <div id="faq3" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        You need exactly 10 trial readings for accurate uncertainty calculation.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                        What is the default tolerance value?
                    </button>
                </h3>
                <div id="faq4" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        The default tolerance is 0.5%. You can adjust it up to a maximum of 1%.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                        How is uncertainty calculated?
                    </button>
                </h3>
                <div id="faq5" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Uncertainty is calculated using u₁ (repeatability), u₂ (reference standard), and u₃ (UUT resolution) components with a 95% confidence level (k=2).
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                        Can I edit test results after saving?
                    </button>
                </h3>
                <div id="faq6" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Yes, you can edit test results using the Edit (✏️) button in the test results list.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                        Why can\'t I generate a certificate?
                    </button>
                </h3>
                <div id="faq7" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        The calibration must be in "Completed" status and all test results must be recorded with uncertainty calculations.
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getQualityDashboardGuide()
{
    return '<h2>Quality Controller Dashboard</h2>

    <h3>Accessing the Dashboard</h3>
    <ol>
        <li>Log in to the system</li>
        <li>Navigate to <strong>Main Menu → Quality Control</strong></li>
        <li>The Quality Dashboard will display</li>
    </ol>

    <h3>Dashboard Components</h3>
    <table>
        <thead><tr><th>Component</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>Pending Checks</td><td>Calibrations waiting for quality review</td></tr>
            <tr><td>Total Checks</td><td>Total calibrations reviewed</td></tr>
            <tr><td>Passed</td><td>Calibrations that passed quality check</td></tr>
            <tr><td>Failed</td><td>Calibrations that failed quality check</td></tr>
            <tr><td>Pending List</td><td>Table showing all pending quality checks</td></tr>
        </tbody>
    </table>

    <div class="alert alert-info">
        <strong>💡 Tip:</strong> The dashboard provides a quick overview of your quality control workload.
    </div>';
}

private function getQualityCheckProcessGuide()
{
    return '<h2>Quality Check Process</h2>

    <h3>Accessing a Quality Check</h3>
    <p><strong>Navigation:</strong> Quality Control → Pending Checks → Click "Check"</p>

    <h3>Review Calibration Information</h3>
    <ul>
        <li>Customer details</li>
        <li>Equipment information</li>
        <li>Serial number</li>
        <li>Technician assigned</li>
        <li>Current status</li>
        <li>Creation date</li>
    </ul>

    <h3>Test Results Summary</h3>
    <ul>
        <li>Total number of tests</li>
        <li>Passed tests</li>
        <li>Failed tests</li>
        <li>Average uncertainty</li>
    </ul>

    <h3>Quality Checklist</h3>
    <table>
        <thead><tr><th>Checklist Item</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>Test Results Complete</td><td>All required tests have been performed</td></tr>
            <tr><td>Uncertainty Calculated</td><td>Uncertainty has been correctly calculated</td></tr>
            <tr><td>Traceability Verified</td><td>Measurements are traceable to standards</td></tr>
            <tr><td>Equipment Calibrated</td><td>MCL equipment is within calibration date</td></tr>
        </tbody>
    </table>

    <div class="alert alert-warning">
        <strong>⚠️ Important:</strong> Always verify all checklist items before making a decision.
    </div>';
}

private function getQualityDecisionGuide()
{
    return '<h2>Quality Decision Guide</h2>

    <h3>Decision Options</h3>

    <div class="card bg-success text-white p-3 mb-3">
        <h4>✅ PASS - Approve Calibration</h4>
        <ul>
            <li>Calibration meets all quality standards</li>
            <li>Certificate can be generated</li>
            <li>Status changes to "Completed"</li>
        </ul>
    </div>

    <div class="card bg-danger text-white p-3 mb-3">
        <h4>❌ FAIL - Reject Calibration</h4>
        <ul>
            <li>Calibration does not meet quality standards</li>
            <li>Certificate cannot be generated</li>
            <li>Status changes to "Rejected"</li>
            <li>Requires rework or investigation</li>
        </ul>
    </div>

    <div class="card bg-warning p-3 mb-3">
        <h4>🔄 NEEDS REVIEW - Return for Rework</h4>
        <ul>
            <li>Issues identified that need correction</li>
            <li>Calibration returned to technician</li>
            <li>Status remains "Testing"</li>
            <li>Technician must address issues</li>
        </ul>
    </div>

    <h3>Quality Notes</h3>
    <p>Always provide clear, detailed notes explaining your decision:</p>
    <ul>
        <li>What was checked and verified</li>
        <li>Any issues found</li>
        <li>Recommendations for improvement</li>
        <li>References to standards or procedures</li>
    </ul>';
}

private function getQualityStandardsGuide()
{
    return '<h2>Quality Standards</h2>

    <h3>Reference Standards</h3>
    <table>
        <thead><tr><th>Standard</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>IEC 62052-11</td><td>Electricity metering equipment - General requirements</td></tr>
            <tr><td>IEC 62058-31</td><td>Electricity metering equipment - Acceptance inspection - Static meters</td></tr>
        </tbody>
    </table>

    <h3>Traceability</h3>
    <p>All measurements must be metrologically traceable through:</p>
    <ul>
        <li>Reference standards certificates</li>
        <li>Calibration dates</li>
        <li>Equipment serial numbers</li>
    </ul>

    <h3>Uncertainty Requirements</h3>
    <ul>
        <li><strong>Confidence Level:</strong> 95%</li>
        <li><strong>Coverage Factor:</strong> k = 2</li>
        <li><strong>Uncertainty Components:</strong>
            <ul>
                <li>u₁ (Repeatability)</li>
                <li>u₂ (Reference Standard)</li>
                <li>u₃ (UUT Resolution)</li>
            </ul>
        </li>
        <li><strong>Combined Uncertainty:</strong> uc = √(u₁² + u₂² + u₃²)</li>
        <li><strong>Expanded Uncertainty:</strong> U = uc × 2</li>
    </ul>

    <div class="alert alert-info">
        <strong>📚 Note:</strong> Always reference these standards in your quality notes.
    </div>';
}

private function getQualityReportsGuide()
{
    return '<h2>Quality Reports</h2>

    <h3>Accessing Quality Reports</h3>
    <p><strong>Navigation:</strong> Quality Control → View Reports</p>

    <h3>Report Statistics</h3>
    <table>
        <thead><tr><th>Statistic</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>Total Checks</td><td>Number of calibrations reviewed</td></tr>
            <tr><td>Passed</td><td>Calibrations that passed quality check</td></tr>
            <tr><td>Failed</td><td>Calibrations that failed quality check</td></tr>
            <tr><td>Pass Rate</td><td>Percentage of calibrations that passed</td></tr>
        </tbody>
    </table>

    <h3>Filtering Reports</h3>
    <p>You can filter reports by:</p>
    <ul>
        <li><strong>Date Range</strong> - From Date and To Date</li>
        <li><strong>Decision</strong> - Passed or Failed</li>
    </ul>

    <h3>Report Table</h3>
    <table>
        <thead><tr><th>Column</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>ID</td><td>Calibration reference number</td></tr>
            <tr><td>Customer</td><td>Customer company name</td></tr>
            <tr><td>Equipment</td><td>Equipment name</td></tr>
            <tr><td>Decision</td><td>Passed/Failed/Pending</td></tr>
            <tr><td>Checked By</td><td>Quality Controller name</td></tr>
            <tr><td>Date</td><td>Date of quality check</td></tr>
        </tbody>
    </table>

    <div class="alert alert-success">
        <strong>📊 Tip:</strong> Use reports to track quality trends and identify areas for improvement.
    </div>';
}
}
