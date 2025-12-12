<?php
require_once 'admin/includes/config.php'; // Contains your PDO connection ($db)
require_once 'includes/header.php';

$page_title = "Contact Us";
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Prepare SQL statement
            $stmt = $db->prepare("INSERT INTO contact_messages 
                                 (name, email, phone, subject, message, created_at) 
                                 VALUES (:name, :email, :phone, :subject, :message, NOW())");
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            
            // Execute query
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Failed to save your message. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Contact Form Error: " . $e->getMessage());
            $error = "A database error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - AgriChem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-section {
            padding: 80px 0;
        }
        
        .contact-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2e7d32, #689f38);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: #689f38;
            box-shadow: 0 0 0 0.25rem rgba(104, 159, 56, 0.25);
        }
        
        .btn-contact {
            background: linear-gradient(135deg, #2e7d32, #689f38);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .btn-contact:hover {
            background: linear-gradient(135deg, #689f38, #2e7d32);
        }
        
        .contact-info-item {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation will be included from header.php -->

    <section class="contact-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-3">Get In Touch</h2>
                    <p class="lead text-muted">Have questions about our agricultural products? Our team is ready to help you.</p>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Thank you for your message! We'll get back to you soon.
                        </div>
                    </div>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="card contact-card h-100 p-4 p-md-5">
                        <h3 class="h4 mb-4">Contact Information</h3>
                        
                        <div class="contact-info-item">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h4 class="h5 mb-2">Our Office</h4>
                                    <p class="mb-0">123 Farm Road, Agricultural Zone<br>City, State 12345</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-4">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <h4 class="h5 mb-2">Phone</h4>
                                    <p class="mb-0">
                                        <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a><br>
                                        <a href="tel:+1987654321" class="text-decoration-none">+1 (987) 654-321</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-4">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h4 class="h5 mb-2">Email</h4>
                                    <p class="mb-0">
                                        <a href="mailto:info@agrichem.com" class="text-decoration-none">info@agrichem.com</a><br>
                                        <a href="mailto:support@agrichem.com" class="text-decoration-none">support@agrichem.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-info-item">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-4">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h4 class="h5 mb-2">Working Hours</h4>
                                    <p class="mb-0">Monday - Friday: 8:00 AM - 6:00 PM<br>
                                    Saturday: 9:00 AM - 2:00 PM<br>
                                    Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card contact-card h-100 p-4 p-md-5">
                        <h3 class="h4 mb-4">Send Us a Message</h3>
                        
                        <form action="contact.php" method="POST">
                            <div class="mb-4">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="subject" class="form-label">Subject</label>
                                <select class="form-select" id="subject" name="subject">
                                    <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Product Information" <?php echo ($_POST['subject'] ?? '') === 'Product Information' ? 'selected' : ''; ?>>Product Information</option>
                                    <option value="Order Support" <?php echo ($_POST['subject'] ?? '') === 'Order Support' ? 'selected' : ''; ?>>Order Support</option>
                                    <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                    <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-contact">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    require_once 'includes/footer.php';
    ?>
</body>
</html>