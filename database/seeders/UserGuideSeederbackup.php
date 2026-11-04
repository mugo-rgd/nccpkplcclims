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
        // Create Categories
        $categories = [
            ['name' => 'Getting Started', 'slug' => 'getting-started', 'icon' => 'rocket', 'order' => 1],
            ['name' => 'Customer Management', 'slug' => 'customers', 'icon' => 'users', 'order' => 2],
            ['name' => 'Calibrations', 'slug' => 'calibrations', 'icon' => 'microscope', 'order' => 3],
            ['name' => 'Certificates', 'slug' => 'certificates', 'icon' => 'certificate', 'order' => 4],
            ['name' => 'Equipment', 'slug' => 'equipment', 'icon' => 'tools', 'order' => 5],
            ['name' => 'FAQ', 'slug' => 'faq', 'icon' => 'question-circle', 'order' => 6],
        ];

        foreach ($categories as $category) {
            GuideCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Get admin user
        $admin = User::first();

        // Create Guides
        $guides = [
            [
                'title' => 'System Overview',
                'slug' => 'system-overview',
                'category' => 'getting-started',
                'order' => 1,
                'content' => $this->getSystemOverview(),
                'icon' => 'info-circle'
            ],
            [
                'title' => 'How to Login',
                'slug' => 'how-to-login',
                'category' => 'getting-started',
                'order' => 2,
                'content' => $this->getLoginGuide(),
                'icon' => 'sign-in-alt'
            ],
            [
                'title' => 'Creating a New Customer',
                'slug' => 'creating-customer',
                'category' => 'customers',
                'order' => 1,
                'content' => $this->getCustomerGuide(),
                'icon' => 'user-plus'
            ],
            [
                'title' => 'Creating a Calibration Request',
                'slug' => 'creating-calibration',
                'category' => 'calibrations',
                'order' => 1,
                'content' => $this->getCalibrationGuide(),
                'icon' => 'plus-circle'
            ],
            [
                'title' => 'Generating Certificates',
                'slug' => 'generating-certificates',
                'category' => 'certificates',
                'order' => 1,
                'content' => $this->getCertificateGuide(),
                'icon' => 'file-pdf'
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'category' => 'faq',
                'order' => 1,
                'content' => $this->getFAQ(),
                'icon' => 'question'
            ],
        ];

        foreach ($guides as $guide) {
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

    private function getSystemOverview()
    {
        return '
        <h2>Welcome to KPLC MCLab System</h2>
        <p>The Meter Central Laboratory System is a comprehensive web-based application designed to manage the entire calibration lifecycle.</p>

        <h3>Key Features</h3>
        <ul>
            <li><strong>Customer Management</strong> - Create, edit, and manage customer records</li>
            <li><strong>Calibration Workflow</strong> - Full lifecycle from request to certificate</li>
            <li><strong>Role-Based Access</strong> - Different permissions for different user roles</li>
            <li><strong>Digital Signatures</strong> - Electronic signatures for certificates</li>
            <li><strong>Document Generation</strong> - PDF generation for all official documents</li>
            <li><strong>Equipment Tracking</strong> - Manage laboratory equipment inventory</li>
            <li><strong>Audit Trail</strong> - Track all system activities</li>
        </ul>

        <h3>System Roles</h3>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Responsibilities</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Super Admin</strong></td>
                    <td>System configuration, user management, overall oversight</td>
                </tr>
                <tr>
                    <td><strong>Lab Manager</strong></td>
                    <td>Approve certificates, oversee operations, manage staff</td>
                </tr>
                <tr>
                    <td><strong>Supervisor</strong></td>
                    <td>Check certificates, verify test results, quality assurance</td>
                </tr>
                <tr>
                    <td><strong>Lab Technician</strong></td>
                    <td>Perform calibrations, record test results, generate certificates</td>
                </tr>
                <tr>
                    <td><strong>Customer Service</strong></td>
                    <td>Create customer accounts, manage requests, handle inquiries</td>
                </tr>
                <tr>
                    <td><strong>Customer</strong></td>
                    <td>Submit requests, view certificates, track calibration status</td>
                </tr>
            </tbody>
        </table>

        <h3>Calibration Workflow</h3>
        <div class="bg-gray-100 p-4 rounded-lg">
            <p class="text-center">
                <strong>External Customers:</strong><br>
                Pending → Quoted → Paid → Assigned → In Progress → Testing → Completed → Certified
            </p>
            <p class="text-center mt-2">
                <strong>Internal (KPLC) Customers:</strong><br>
                Pending → Assigned → In Progress → Testing → Completed → Certified
            </p>
        </div>
        ';
    }

    private function getLoginGuide()
    {
        return '
        <h2>How to Login to the System</h2>

        <h3>Step 1: Open Your Browser</h3>
        <p>Open Google Chrome (recommended), Mozilla Firefox, or Microsoft Edge.</p>

        <h3>Step 2: Navigate to the System</h3>
        <p>Enter the system URL in your browser\'s address bar:</p>
        <div class="bg-gray-100 p-3 rounded">
            <code>http://localhost:8000/login</code> <span class="text-sm text-gray-500">(Development)</span><br>
            <code>https://yourdomain.com/login</code> <span class="text-sm text-gray-500">(Production)</span>
        </div>

        <h3>Step 3: Enter Your Credentials</h3>
        <div class="bg-gray-100 p-4 rounded-lg">
            <div class="mb-2"><strong>Email:</strong> your.email@kplc.co.ke</div>
            <div class="mb-2"><strong>Password:</strong> your_secure_password</div>
        </div>

        <h3>Default Login Credentials</h3>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Super Admin</td><td>admin@kplc.co.ke</td><td>Admin@123</td></tr>
                <tr><td>Lab Manager</td><td>labmanager@kplc.co.ke</td><td>Manager@123</td></tr>
                <tr><td>Supervisor</td><td>supervisor@kplc.co.ke</td><td>Supervisor@123</td></tr>
                <tr><td>Lab Technician</td><td>tech@kplc.co.ke</td><td>Tech@123</td></tr>
                <tr><td>Customer</td><td>customer@kplc.co.ke</td><td>Customer@123</td></tr>
            </tbody>
        </table>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
            <p class="text-yellow-700">
                <strong>⚠️ Important:</strong> Change your password immediately after first login!
            </p>
        </div>

        <h3>Step 4: Click Login</h3>
        <p>After entering your credentials, click the <strong>Login</strong> button.</p>

        <h3>How to Logout</h3>
        <ol>
            <li>Click on your name in the top-right corner</li>
            <li>Select <strong>Logout</strong> from the dropdown menu</li>
        </ol>
        ';
    }

    private function getCustomerGuide()
    {
        return '
        <h2>Creating a New Customer</h2>

        <h3>Navigation</h3>
        <p>Main Menu → Customers → Add Customer</p>

        <h3>Step 1: Fill Customer Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Company Name</td>
                    <td>✅ Yes</td>
                    <td>Full company name</td>
                </tr>
                <tr>
                    <td>Contact Person</td>
                    <td>✅ Yes</td>
                    <td>Primary contact name</td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>✅ Yes</td>
                    <td>Contact phone number</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>❌ No</td>
                    <td>Customer email (for notifications)</td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>✅ Yes</td>
                    <td>Physical address</td>
                </tr>
                <tr>
                    <td>Tax ID (PIN)</td>
                    <td>❌ No</td>
                    <td>KRA PIN number</td>
                </tr>
                <tr>
                    <td>Credit Limit</td>
                    <td>❌ No</td>
                    <td>Credit limit amount</td>
                </tr>
                <tr>
                    <td>Payment Terms</td>
                    <td>❌ No</td>
                    <td>30/60/90 days</td>
                </tr>
            </tbody>
        </table>

        <h3>Step 2: Click "Create Customer"</h3>
        <p>Review all information and click the <strong>Create Customer</strong> button.</p>

        <div class="bg-green-50 border-l-4 border-green-400 p-4 mt-4">
            <p class="text-green-700">
                <strong>✅ Success:</strong> The customer will be created and ready for calibration requests.
            </p>
        </div>

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
        </ul>
        ';
    }

    private function getCalibrationGuide()
    {
        return '
        <h2>Creating a Calibration Request</h2>

        <h3>Navigation</h3>
        <p>Main Menu → Calibrations → New Calibration</p>

        <h3>Step 1: Select Customer</h3>
        <ul>
            <li>Choose the customer from the dropdown list</li>
            <li>If customer doesn\'t exist, create one first</li>
        </ul>

        <h3>Step 2: Enter Equipment Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Equipment Name</td><td>✅ Yes</td><td>Name of equipment</td></tr>
                <tr><td>Serial Number</td><td>✅ Yes</td><td>Unique serial number</td></tr>
                <tr><td>Manufacturer</td><td>❌ No</td><td>Equipment manufacturer</td></tr>
                <tr><td>Model</td><td>❌ No</td><td>Equipment model</td></tr>
                <tr><td>Accuracy Class</td><td>❌ No</td><td>Accuracy classification</td></tr>
                <tr><td>Condition</td><td>✅ Yes</td><td>Excellent/Good/Fair/Poor/Damaged</td></tr>
            </tbody>
        </table>

        <h3>Step 3: Select Service Types</h3>
        <ul>
            <li>Check one or more service types from the list</li>
        </ul>

        <h3>Step 4: Select Equipment Type & Rate</h3>
        <ul>
            <li>Choose the equipment type</li>
            <li>The rate will auto-populate based on selection</li>
        </ul>

        <h3>Step 5: Select MCL Equipment</h3>
        <ul>
            <li>Choose the laboratory equipment used for testing</li>
        </ul>

        <h3>Step 6: Calibration Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Scheduled Date</td>
                    <td>❌ No</td>
                    <td>Date and time of scheduled calibration</td>
                </tr>
                <tr>
                    <td>Technician</td>
                    <td>❌ No</td>
                    <td>Assign a technician (or later)</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>✅ Yes</td>
                    <td>Laboratory or On-Site</td>
                </tr>
            </tbody>
        </table>

        <h3>Step 7: Click "Create Calibration"</h3>
        <p>Review all information and click the <strong>Create Calibration</strong> button.</p>

        <h3>Calibration Status Flow</h3>
        <div class="bg-gray-100 p-4 rounded-lg">
            <p><strong>External Customers:</strong></p>
            <p class="text-center">Pending → Quoted → Paid → Assigned → In Progress → Testing → Completed → Certified</p>
            <p class="mt-2"><strong>Internal (KPLC) Customers:</strong></p>
            <p class="text-center">Pending → Assigned → In Progress → Testing → Completed → Certified</p>
        </div>
        ';
    }

    private function getCertificateGuide()
    {
        return '
        <h2>Generating Certificates</h2>

        <h3>Prerequisites</h3>
        <ul>
            <li>Calibration must be in <strong>"Completed"</strong> status</li>
            <li>All test results must be recorded</li>
        </ul>

        <h3>Step 1: Generate Certificate</h3>
        <ol>
            <li>Open the calibration details</li>
            <li>Click <strong>Generate Certificate</strong></li>
            <li>The certificate is created automatically</li>
            <li>You will be redirected to the certificate view</li>
        </ol>

        <h3>Step 2: Upload Test Report</h3>
        <ol>
            <li>Open the certificate</li>
            <li>Click <strong>Upload Test Report</strong></li>
            <li>Select the PDF test report from the machine</li>
            <li>Enter machine details:
                <ul>
                    <li>Machine Used</li>
                    <li>Machine Serial Number</li>
                    <li>Test Engineer</li>
                    <li>Test Date</li>
                </ul>
            </li>
            <li>Click <strong>Upload & Attach</strong></li>
        </ol>

        <h3>Certificate Workflow</h3>
        <div class="bg-gray-100 p-4 rounded-lg">
            <p class="text-center">
                Technician Generates Certificate<br>
                ↓<br>
                Upload Test Report (from machine)<br>
                ↓<br>
                Supervisor Checks & Signs<br>
                ↓<br>
                Lab Manager Approves & Signs<br>
                ↓<br>
                Send to Customer
            </p>
        </div>

        <h3>Certificate Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Generated</td>
                    <td>Certificate created</td>
                    <td>Supervisor to check</td>
                </tr>
                <tr>
                    <td>Checked</td>
                    <td>Supervisor reviewed</td>
                    <td>Lab Manager to approve</td>
                </tr>
                <tr>
                    <td>Approved</td>
                    <td>Lab Manager approved</td>
                    <td>Send to customer</td>
                </tr>
                <tr>
                    <td>Sent</td>
                    <td>Sent to customer</td>
                    <td>Complete</td>
                </tr>
            </tbody>
        </table>

        <h3>Downloading Documents</h3>
        <p>From the certificate view, you can download:</p>
        <ul>
            <li><strong>Certificate</strong> - Main calibration certificate (PDF)</li>
            <li><strong>Test Report</strong> - Machine-generated test report (PDF)</li>
            <li><strong>Combined</strong> - Both certificate and test report (PDF)</li>
        </ul>
        ';
    }

    private function getFAQ()
    {
        return '
        <h2>Frequently Asked Questions</h2>

        <div class="space-y-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">General Questions</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="font-medium text-gray-700">Q: How do I reset my password?</p>
                        <p class="text-gray-600">A: Click "Forgot Password" on the login page and follow the instructions.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: What should I do if the system is slow?</p>
                        <p class="text-gray-600">A: Clear your browser cache or try a different browser.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: What are the system requirements?</p>
                        <p class="text-gray-600">A: Google Chrome (recommended), screen resolution 1366x768 or higher, and a PDF reader.</p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">Calibration Questions</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="font-medium text-gray-700">Q: Why can\'t I assign a technician to a calibration?</p>
                        <p class="text-gray-600">A: Ensure the calibration is in "Pending" (Internal) or "Paid" (External) status.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: Why can\'t I generate a certificate?</p>
                        <p class="text-gray-600">A: The calibration must be in "Completed" status first.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: How long does a calibration take?</p>
                        <p class="text-gray-600">A: Standard calibration takes 2-5 business days depending on the equipment type.</p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">Certificate Questions</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="font-medium text-gray-700">Q: How do I add my digital signature?</p>
                        <p class="text-gray-600">A: Contact your system administrator to upload your signature image.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: Why is my certificate showing "Pending" for signatures?</p>
                        <p class="text-gray-600">A: The certificate needs to be checked and approved by the supervisor and lab manager.</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Q: Can I download certificates in bulk?</p>
                        <p class="text-gray-600">A: Yes, select multiple certificates and use the "Bulk Download" option.</p>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-6">Need More Help?</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800">
                <strong>Contact Support:</strong><br>
                Email: support@kplc.co.ke<br>
                Phone: 0722 225 313
            </p>
        </div>
        ';
    }
}
