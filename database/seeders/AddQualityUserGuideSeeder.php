<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddQualityUserGuideSeeder extends Seeder
{
    public function run()
    {
        // First, ensure categories exist
        $this->ensureCategories();

        // Get category IDs
        $categories = DB::table('guide_categories')->pluck('id', 'slug')->toArray();

        $guides = [
            [
                'title' => 'System Overview',
                'slug' => 'system-overview',
                'category' => $categories['getting-started'] ?? 'getting-started',
                'content' => $this->getOverviewContent(),
                'content_html' => $this->getOverviewContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Getting Started Guide',
                'slug' => 'getting-started-guide',
                'category' => $categories['getting-started'] ?? 'getting-started',
                'content' => $this->getGettingStartedContent(),
                'content_html' => $this->getGettingStartedContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Dashboard Guide',
                'slug' => 'dashboard-guide',
                'category' => $categories['dashboard'] ?? 'dashboard',
                'content' => $this->getDashboardContent(),
                'content_html' => $this->getDashboardContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Managing Quality Objectives',
                'slug' => 'managing-quality-objectives',
                'category' => $categories['objectives'] ?? 'objectives',
                'content' => $this->getObjectivesContent(),
                'content_html' => $this->getObjectivesContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Recording Achievements',
                'slug' => 'recording-achievements',
                'category' => $categories['achievements'] ?? 'achievements',
                'content' => $this->getAchievementsContent(),
                'content_html' => $this->getAchievementsContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Managing Corrective Actions',
                'slug' => 'managing-corrective-actions',
                'category' => $categories['corrective-actions'] ?? 'corrective-actions',
                'content' => $this->getCorrectiveActionsContent(),
                'content_html' => $this->getCorrectiveActionsContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Generating Reports',
                'slug' => 'generating-reports',
                'category' => $categories['reports'] ?? 'reports',
                'content' => $this->getReportsContent(),
                'content_html' => $this->getReportsContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'category' => $categories['faq'] ?? 'faq',
                'content' => $this->getFAQContent(),
                'content_html' => $this->getFAQContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Troubleshooting Guide',
                'slug' => 'troubleshooting-guide',
                'category' => $categories['troubleshooting'] ?? 'troubleshooting',
                'content' => $this->getTroubleshootingContent(),
                'content_html' => $this->getTroubleshootingContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Glossary of Terms',
                'slug' => 'glossary-of-terms',
                'category' => $categories['glossary'] ?? 'glossary',
                'content' => $this->getGlossaryContent(),
                'content_html' => $this->getGlossaryContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'User Roles & Permissions',
                'slug' => 'user-roles-permissions',
                'category' => $categories['user-roles'] ?? 'user-roles',
                'content' => $this->getRolesContent(),
                'content_html' => $this->getRolesContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Getting Support',
                'slug' => 'getting-support',
                'category' => $categories['support'] ?? 'support',
                'content' => $this->getSupportContent(),
                'content_html' => $this->getSupportContent(),
                'is_published' => 1,
                'views' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        $count = 0;
        $skipped = 0;

        foreach ($guides as $guide) {
            // Check if guide already exists
            $existing = DB::table('user_guide')->where('slug', $guide['slug'])->first();

            if (!$existing) {
                // Insert new guide
                DB::table('user_guide')->insert($guide);
                $count++;
            } else {
                $skipped++;
            }
        }

        $this->command->info('✅ Quality User Guides added successfully!');
        $this->command->info('📚 ' . $count . ' new guides added.');
        $this->command->info('⏭️ ' . $skipped . ' guides already exist (skipped).');
    }

    private function ensureCategories()
    {
        $categories = [
            ['name' => 'Getting Started', 'slug' => 'getting-started', 'description' => 'Learn how to get started', 'icon' => 'fa-rocket', 'order' => 1, 'is_active' => 1],
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'description' => 'Understanding the dashboard', 'icon' => 'fa-chart-pie', 'order' => 2, 'is_active' => 1],
            ['name' => 'Quality Objectives', 'slug' => 'objectives', 'description' => 'Managing quality objectives', 'icon' => 'fa-bullseye', 'order' => 3, 'is_active' => 1],
            ['name' => 'Achievements', 'slug' => 'achievements', 'description' => 'Tracking achievements', 'icon' => 'fa-chart-line', 'order' => 4, 'is_active' => 1],
            ['name' => 'Corrective Actions', 'slug' => 'corrective-actions', 'description' => 'Managing CARs', 'icon' => 'fa-tasks', 'order' => 5, 'is_active' => 1],
            ['name' => 'Reports', 'slug' => 'reports', 'description' => 'Generating reports', 'icon' => 'fa-file-alt', 'order' => 6, 'is_active' => 1],
            ['name' => 'User Roles', 'slug' => 'user-roles', 'description' => 'User roles and permissions', 'icon' => 'fa-users-cog', 'order' => 7, 'is_active' => 1],
            ['name' => 'FAQ', 'slug' => 'faq', 'description' => 'Frequently asked questions', 'icon' => 'fa-question-circle', 'order' => 8, 'is_active' => 1],
            ['name' => 'Troubleshooting', 'slug' => 'troubleshooting', 'description' => 'Common issues and solutions', 'icon' => 'fa-wrench', 'order' => 9, 'is_active' => 1],
            ['name' => 'Glossary', 'slug' => 'glossary', 'description' => 'Key terms and definitions', 'icon' => 'fa-book', 'order' => 10, 'is_active' => 1],
            ['name' => 'Support', 'slug' => 'support', 'description' => 'Getting help and support', 'icon' => 'fa-headset', 'order' => 11, 'is_active' => 1]
        ];

        foreach ($categories as $category) {
            $exists = DB::table('guide_categories')->where('slug', $category['slug'])->first();
            if (!$exists) {
                DB::table('guide_categories')->insert(array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    // Content methods
    private function getOverviewContent()
    {
        return '<h2>System Overview</h2>
<p>The Quality Management System (QMS) is designed to help the Meter Central Laboratory track, monitor, and improve quality performance against established objectives.</p>

<h3>Purpose</h3>
<p>The system provides:</p>
<ul>
    <li>Real-time monitoring of quality objectives</li>
    <li>Tracking of quarterly achievements</li>
    <li>Management of corrective actions</li>
    <li>Comprehensive reporting capabilities</li>
    <li>ISO 17025 compliance support</li>
</ul>

<h3>Key Features</h3>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="fas fa-chart-pie text-primary"></i> Dashboard</h6>
                <p class="small text-muted">Real-time performance visualization</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="fas fa-bullseye text-success"></i> Objectives Management</h6>
                <p class="small text-muted">Create and track quality objectives</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="fas fa-chart-line text-info"></i> Achievements Tracking</h6>
                <p class="small text-muted">Record quarterly performance</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6><i class="fas fa-tasks text-warning"></i> Corrective Actions</h6>
                <p class="small text-muted">Manage CARs for non-conformances</p>
            </div>
        </div>
    </div>
</div>

<h3>System Requirements</h3>
<ul>
    <li>Modern web browser (Chrome, Firefox, Edge, Safari)</li>
    <li>Internet connection</li>
    <li>Valid user credentials</li>
</ul>';
    }

    private function getGettingStartedContent()
    {
        return '<h2>Getting Started Guide</h2>

<h3>Login</h3>
<ol>
    <li>Navigate to the system URL</li>
    <li>Enter your credentials:
        <ul>
            <li><strong>Email:</strong> your.email@kplc.co.ke</li>
            <li><strong>Password:</strong> your password</li>
        </ul>
    </li>
    <li>Click <strong>Login</strong></li>
</ol>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Default Test Account:</strong><br>
    Email: quality@kplc.co.ke<br>
    Password: Quality@123<br>
    Role: Quality Controller
</div>

<h3>Navigation</h3>
<p>The system uses a two-level navigation system:</p>

<h4>Top Navigation Bar</h4>
<ul>
    <li><strong>Dashboard:</strong> Main dashboard</li>
    <li><strong>Calibrations:</strong> Calibration management</li>
    <li><strong>Customers:</strong> Customer management</li>
    <li><strong>Certificates:</strong> Certificate management</li>
    <li><strong>Quality:</strong> Quality management dropdown
        <ul>
            <li>Dashboard</li>
            <li>Objectives</li>
            <li>Achievements</li>
            <li>Corrective Actions</li>
            <li>Reports</li>
        </ul>
    </li>
    <li><strong>User Guide:</strong> System documentation</li>
</ul>

<h4>Sidebar Navigation</h4>
<p>Quick access to all major features organized by category.</p>

<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <strong>Tip:</strong> Use the sidebar for quick navigation and the top menu for dropdown access.
</div>';
    }

    private function getDashboardContent()
    {
        return '<h2>Dashboard Guide</h2>

<h3>Overview</h3>
<p>The dashboard provides a real-time snapshot of quality performance.</p>

<h3>Dashboard Components</h3>

<h4>KPI Cards</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Card</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge bg-primary">Overall Achievement</span></td>
                <td>Average performance across all objectives</td>
            </tr>
            <tr>
                <td><span class="badge bg-success">On Track</span></td>
                <td>Objectives meeting targets (≥95%)</td>
            </tr>
            <tr>
                <td><span class="badge bg-warning">At Risk</span></td>
                <td>Objectives below target (80-94%)</td>
            </tr>
            <tr>
                <td><span class="badge bg-danger">Not Met</span></td>
                <td>Objectives significantly below target (&lt;80%)</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Quick Actions</h4>
<ul>
    <li><strong>New Objective:</strong> Create a new quality objective</li>
    <li><strong>Record Achievement:</strong> Enter quarterly performance</li>
    <li><strong>Create CAR:</strong> Open a corrective action</li>
    <li><strong>Generate Report:</strong> Create quality report</li>
</ul>

<h3>Dashboard Features</h3>
<ul>
    <li><strong>FYR Selector:</strong> Switch between financial years</li>
    <li><strong>Refresh Button:</strong> Update dashboard data</li>
    <li><strong>Print/Save:</strong> Print dashboard view</li>
</ul>';
    }

    private function getObjectivesContent()
    {
        return '<h2>Managing Quality Objectives</h2>

<h3>Overview</h3>
<p>Quality objectives are specific, measurable targets that align with the laboratory\'s quality policy and ISO 17025 requirements.</p>

<h3>Creating a New Objective</h3>
<ol>
    <li>Navigate to <strong>Quality → Objectives</strong> or click <strong>New Objective</strong></li>
    <li>Fill in the required fields</li>
    <li>Click <strong>Create Objective</strong></li>
</ol>

<h3>Required Fields</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Field</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Objective ID</strong></td>
                <td>Unique identifier (e.g., MCL-001)</td>
            </tr>
            <tr>
                <td><strong>Section</strong></td>
                <td>MCL, Instruments, or Large Power</td>
            </tr>
            <tr>
                <td><strong>Description</strong></td>
                <td>Detailed description of the objective</td>
            </tr>
            <tr>
                <td><strong>Unit of Measure</strong></td>
                <td>Days, Hours, NO., %, Number</td>
            </tr>
            <tr>
                <td><strong>Target Value</strong></td>
                <td>Numeric target to achieve</td>
            </tr>
            <tr>
                <td><strong>Target FYR</strong></td>
                <td>Financial year for this objective</td>
            </tr>
            <tr>
                <td><strong>Owner</strong></td>
                <td>Person responsible</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Viewing and Managing Objectives</h3>
<ul>
    <li><strong>Search and Filter:</strong> Use filters by section, FYR, status</li>
    <li><strong>Edit:</strong> Click the edit button to modify objective details</li>
    <li><strong>Delete:</strong> Remove objective with confirmation</li>
</ul>';
    }

    private function getAchievementsContent()
    {
        return '<h2>Recording Achievements</h2>

<h3>Overview</h3>
<p>Quarterly achievements record actual performance against objectives for each quarter of the FYR.</p>

<h3>Recording an Achievement</h3>
<ol>
    <li>Navigate to <strong>Quality → Achievements</strong></li>
    <li>Click <strong>Record Achievement</strong></li>
    <li>Select the objective, FYR, and quarter</li>
    <li>Enter the actual achievement value</li>
    <li>Click <strong>Save Achievement</strong></li>
</ol>

<h3>Bulk Entry</h3>
<ol>
    <li>Navigate to <strong>Quality → Achievements</strong></li>
    <li>Click <strong>Bulk Entry</strong></li>
    <li>Select FYR and Quarter</li>
    <li>Enter values for multiple objectives</li>
    <li>Click <strong>Save All Achievements</strong></li>
</ol>

<h3>Achievement Status</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Status</th>
                <th>Criteria</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge bg-success">On Track</span></td>
                <td>≥95% of target</td>
                <td>Green</td>
            </tr>
            <tr>
                <td><span class="badge bg-warning">At Risk</span></td>
                <td>80-94% of target</td>
                <td>Yellow</td>
            </tr>
            <tr>
                <td><span class="badge bg-danger">Not Met</span></td>
                <td>&lt;80% of target</td>
                <td>Red</td>
            </tr>
            <tr>
                <td><span class="badge bg-secondary">Pending</span></td>
                <td>No data recorded</td>
                <td>Gray</td>
            </tr>
        </tbody>
    </table>
</div>';
    }

    private function getCorrectiveActionsContent()
    {
        return '<h2>Managing Corrective Actions (CARs)</h2>

<h3>Overview</h3>
<p>Corrective Actions document and track actions taken to address quality issues or non-conformances.</p>

<h3>CAR Lifecycle</h3>
<div class="text-center my-4">
    <div class="d-flex justify-content-center align-items-center">
        <span class="badge bg-danger p-3 mx-2">Open</span>
        <i class="fas fa-arrow-right"></i>
        <span class="badge bg-warning p-3 mx-2">In Progress</span>
        <i class="fas fa-arrow-right"></i>
        <span class="badge bg-success p-3 mx-2">Closed</span>
    </div>
</div>

<h3>Creating a CAR</h3>
<ol>
    <li>Navigate to <strong>Quality → Corrective Actions</strong></li>
    <li>Click <strong>New Corrective Action</strong></li>
    <li>Fill in the required fields</li>
    <li>Click <strong>Create CAR</strong></li>
</ol>

<h3>Managing CARs</h3>
<ul>
    <li><strong>Open → In Progress:</strong> Click "Start Progress"</li>
    <li><strong>In Progress → Closed:</strong> Click "Approve & Close"</li>
</ul>

<h3>CAR Status Colors</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Status</th>
                <th>Color</th>
                <th>Meaning</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge bg-danger">Open</span></td>
                <td>Red</td>
                <td>Issue identified, action needed</td>
            </tr>
            <tr>
                <td><span class="badge bg-warning">In Progress</span></td>
                <td>Yellow</td>
                <td>Action being taken</td>
            </tr>
            <tr>
                <td><span class="badge bg-success">Closed</span></td>
                <td>Green</td>
                <td>Issue resolved</td>
            </tr>
        </tbody>
    </table>
</div>';
    }

    private function getReportsContent()
    {
        return '<h2>Generating Reports</h2>

<h3>Overview</h3>
<p>Generate comprehensive quality reports for management review and audit purposes.</p>

<h3>Generating a Report</h3>
<ol>
    <li>Navigate to <strong>Quality → Reports</strong></li>
    <li>Select report parameters:
        <ul>
            <li><strong>FYR Period:</strong> Select the financial year</li>
            <li><strong>Section:</strong> All, MCL, Instruments, Large Power</li>
            <li><strong>Status Filter:</strong> All, On Track, At Risk, Not Met, Pending</li>
            <li><strong>Format:</strong> HTML or PDF</li>
        </ul>
    </li>
    <li>Click <strong>Generate Report</strong></li>
</ol>

<h3>Report Components</h3>
<ul>
    <li><strong>Header:</strong> FYR, section, status filter</li>
    <li><strong>Summary:</strong> Total, On Track, At Risk, Not Met</li>
    <li><strong>Performance Summary:</strong> Overall achievement and distribution</li>
    <li><strong>Section Analysis:</strong> Performance by section</li>
    <li><strong>Detailed Objectives:</strong> Complete objective list with performance</li>
</ul>

<h3>Export Options</h3>
<ul>
    <li><strong>Print:</strong> Use browser print (Ctrl+P)</li>
    <li><strong>PDF:</strong> Export to PDF format</li>
    <li><strong>HTML:</strong> View online with formatting</li>
</ul>';
    }

    private function getFAQContent()
    {
        return '<h2>Frequently Asked Questions</h2>

<h3>General</h3>

<div class="accordion" id="faqAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                How do I reset my password?
            </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
                Contact your system administrator to reset your password.
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                What is FYR?
            </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
                FYR stands for Financial Year. The laboratory uses July-June financial years (e.g., 2023-2024).
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                What are the different sections?
            </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
                Three main sections: MCL, Instruments, and Large Power.
            </div>
        </div>
    </div>
</div>

<h3>Objectives</h3>

<div class="accordion" id="faqObjectives">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#obj1">
                Why can\'t I edit the Objective ID and Section?
            </button>
        </h2>
        <div id="obj1" class="accordion-collapse collapse show" data-bs-parent="#faqObjectives">
            <div class="accordion-body">
                These are immutable identifiers. Create a new objective if changes are needed.
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#obj2">
                What is the baseline value?
            </button>
        </h2>
        <div id="obj2" class="accordion-collapse collapse" data-bs-parent="#faqObjectives">
            <div class="accordion-body">
                The baseline is the previous year\'s achievement, used as a reference point.
            </div>
        </div>
    </div>
</div>

<h3>Corrective Actions</h3>

<div class="accordion" id="faqCARs">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#car1">
                When should I create a CAR?
            </button>
        </h2>
        <div id="car1" class="accordion-collapse collapse show" data-bs-parent="#faqCARs">
            <div class="accordion-body">
                When an objective is At Risk or Not Met, or when any quality issue arises.
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#car2">
                Can I reopen a closed CAR?
            </button>
        </h2>
        <div id="car2" class="accordion-collapse collapse" data-bs-parent="#faqCARs">
            <div class="accordion-body">
                Edit the CAR and change status back to Open or In Progress.
            </div>
        </div>
    </div>
</div>';
    }

    private function getTroubleshootingContent()
    {
        return '<h2>Troubleshooting Guide</h2>

<h3>Common Issues</h3>

<h4>Login Issues</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Issue</th>
                <th>Solution</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Incorrect password</td>
                <td>Reset password via administrator</td>
            </tr>
            <tr>
                <td>Account locked</td>
                <td>Contact system administrator</td>
            </tr>
            <tr>
                <td>Access denied</td>
                <td>Verify user role and permissions</td>
            </tr>
        </tbody>
    </table>
</div>

<h4>Dashboard Issues</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Issue</th>
                <th>Solution</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>No data displayed</td>
                <td>Ensure objectives have been created</td>
            </tr>
            <tr>
                <td>Charts not loading</td>
                <td>Refresh page or clear browser cache</td>
            </tr>
            <tr>
                <td>Slow performance</td>
                <td>Reduce data range or use filters</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Error Messages</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Error</th>
                <th>Meaning</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>"Duplicate entry"</td>
                <td>Record already exists</td>
                <td>Check for existing record</td>
            </tr>
            <tr>
                <td>"View not found"</td>
                <td>Missing template</td>
                <td>Contact administrator</td>
            </tr>
            <tr>
                <td>"Permission denied"</td>
                <td>Insufficient access</td>
                <td>Verify user role</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Performance Tips</h3>
<ol>
    <li><strong>Use Filters:</strong> Filter data to improve load times</li>
    <li><strong>Clear Cache:</strong> Regularly clear browser cache</li>
    <li><strong>Modern Browser:</strong> Use updated Chrome or Firefox</li>
    <li><strong>Check Permissions:</strong> Verify access rights</li>
</ol>';
    }

    private function getGlossaryContent()
    {
        return '<h2>Glossary of Terms</h2>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Term</th>
                <th>Definition</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>FYR</strong></td>
                <td>Financial Year (July-June)</td>
            </tr>
            <tr>
                <td><strong>QMS</strong></td>
                <td>Quality Management System</td>
            </tr>
            <tr>
                <td><strong>CAR</strong></td>
                <td>Corrective Action Request</td>
            </tr>
            <tr>
                <td><strong>MCL</strong></td>
                <td>Meter Central Laboratory</td>
            </tr>
            <tr>
                <td><strong>TAT</strong></td>
                <td>Turnaround Time</td>
            </tr>
            <tr>
                <td><strong>KPI</strong></td>
                <td>Key Performance Indicator</td>
            </tr>
            <tr>
                <td><strong>ISO</strong></td>
                <td>International Organization for Standardization</td>
            </tr>
            <tr>
                <td><strong>ISO 17025</strong></td>
                <td>General requirements for the competence of testing and calibration laboratories</td>
            </tr>
            <tr>
                <td><strong>Non-conformance</strong></td>
                <td>Failure to meet specified requirements</td>
            </tr>
            <tr>
                <td><strong>Root Cause</strong></td>
                <td>The underlying reason for a non-conformance</td>
            </tr>
            <tr>
                <td><strong>Corrective Action</strong></td>
                <td>Action to eliminate the cause of a non-conformance</td>
            </tr>
        </tbody>
    </table>
</div>';
    }

    private function getRolesContent()
    {
        return '<h2>User Roles & Permissions</h2>

<h3>Role Overview</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Role</th>
                <th>Description</th>
                <th>Access Level</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge bg-danger">Super Admin</span></td>
                <td>Full system access</td>
                <td>All features</td>
            </tr>
            <tr>
                <td><span class="badge bg-primary">Admin</span></td>
                <td>System administration</td>
                <td>User management, audit logs</td>
            </tr>
            <tr>
                <td><span class="badge bg-info">Lab Manager</span></td>
                <td>Lab management</td>
                <td>Full quality management</td>
            </tr>
            <tr>
                <td><span class="badge bg-success">Quality Controller</span></td>
                <td>Quality operations</td>
                <td>Full quality features</td>
            </tr>
            <tr>
                <td><span class="badge bg-secondary">Supervisor</span></td>
                <td>Team supervision</td>
                <td>View and create</td>
            </tr>
            <tr>
                <td><span class="badge bg-secondary">Lab Technician</span></td>
                <td>Technical operations</td>
                <td>Basic access</td>
            </tr>
            <tr>
                <td><span class="badge bg-secondary">Customer</span></td>
                <td>External user</td>
                <td>Limited access</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Quality Controller Permissions</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Module</th>
                <th>Permissions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Dashboard</strong></td>
                <td>View</td>
            </tr>
            <tr>
                <td><strong>Objectives</strong></td>
                <td>View, Create, Edit, Delete</td>
            </tr>
            <tr>
                <td><strong>Achievements</strong></td>
                <td>View, Create, Edit, Delete, Bulk Entry</td>
            </tr>
            <tr>
                <td><strong>Corrective Actions</strong></td>
                <td>View, Create, Edit, Delete, Approve</td>
            </tr>
            <tr>
                <td><strong>Reports</strong></td>
                <td>View, Generate, Export</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Role Assignment</h3>
<p>Roles are assigned by Super Admin or Admin users through:</p>
<ol>
    <li><strong>Users</strong> → <strong>Edit User</strong></li>
    <li>Select role from dropdown</li>
    <li>Save changes</li>
</ol>';
    }

    private function getSupportContent()
    {
        return '<h2>Getting Support</h2>

<h3>Getting Help</h3>
<ul>
    <li><strong>System Administrator:</strong> For login, permissions, and technical issues</li>
    <li><strong>Lab Manager:</strong> For quality processes and procedures</li>
    <li><strong>Quality Manager:</strong> For quality policy and ISO compliance</li>
</ul>

<h3>Contact Information</h3>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Role</th>
                <th>Email</th>
                <th>Purpose</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>System Admin</strong></td>
                <td>admin@kplc.co.ke</td>
                <td>Technical support</td>
            </tr>
            <tr>
                <td><strong>Lab Manager</strong></td>
                <td>labmanager@kplc.co.ke</td>
                <td>Quality procedures</td>
            </tr>
            <tr>
                <td><strong>Quality Manager</strong></td>
                <td>quality@kplc.co.ke</td>
                <td>Quality policy</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>Training Resources</h3>
<ul>
    <li><strong>User Guide:</strong> This documentation</li>
    <li><strong>Video Tutorials:</strong> Available upon request</li>
    <li><strong>Training Sessions:</strong> Scheduled as needed</li>
    <li><strong>Quick Reference Cards:</strong> Available for key tasks</li>
</ul>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle"></i>
    <strong>Document Version:</strong> 1.0<br>
    <strong>Last Updated:</strong> ' . date('F d, Y') . '<br>
    <strong>Prepared By:</strong> Quality Management Team
</div>';
    }
}
