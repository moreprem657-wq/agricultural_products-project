<?php
require_once 'admin/includes/config.php';
require_once 'includes/header.php';

$page_title = "Home";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - AgriChem</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .shadow-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .feature-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .category-card .card-img-top {
            background-position: center;
        }
        
        .user-icon-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            border-radius: 50%;
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- Hero Section with Modern Design -->
<section class="hero-section bg-gradient-success text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-4">Welcome to <span class="text-warning">AgriChem</span></h1>
                <p class="lead mb-4">Your trusted partner for premium agricultural solutions that maximize yield and efficiency.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="products.php" class="btn btn-light btn-lg px-4 py-2 rounded-pill fw-bold">View Products</a>
                    <a href="#" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill">Learn More</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="text-center">
                    <i class="fas fa-tractor fs-1 text-white opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 rounded-3 shadow-sm bg-white text-center">
                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-seedling fs-3"></i>
                    </div>
                    <h3 class="h5 mb-3">Quality Products</h3>
                    <p class="mb-0">Premium agricultural chemicals tested for effectiveness and safety.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 rounded-3 shadow-sm bg-white text-center">
                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-truck fs-3"></i>
                    </div>
                    <h3 class="h5 mb-3">Fast Delivery</h3>
                    <p class="mb-0">Reliable nationwide delivery to get you what you need, when you need it.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 rounded-3 shadow-sm bg-white text-center">
                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-headset fs-3"></i>
                    </div>
                    <h3 class="h5 mb-3">Expert Support</h3>
                    <p class="mb-0">Our agricultural specialists are ready to assist you with any questions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Our Product Categories</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Browse our specialized range of agricultural solutions designed to meet your farming needs.</p>
        </div>
        
        <div class="row g-4">
            <?php
            $categories = getCategories();
            foreach ($categories as $category):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="category-card card border-0 h-100 overflow-hidden shadow-hover">
                    <div class="card-img-top bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 180px;">
                        <i class="fas fa-spray-can fs-1 text-success opacity-50"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="h5 card-title fw-bold"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>...</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="products.php?category=<?php echo $category['category_id']; ?>" class="btn btn-outline-success stretched-link">Explore Products</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-success btn-lg px-4 py-2">View All Products</a>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">What Our Customers Say</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Trusted by farmers and agricultural businesses nationwide</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100 rounded-3 bg-white shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="user-icon-placeholder">
                                <i class="fas fa-user fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">John Farmer</h5>
                            <small class="text-muted">Wheat Grower</small>
                        </div>
                    </div>
                    <p class="mb-0">"AgriChem's products have increased my yield by 20% this season. Their expert advice was invaluable."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100 rounded-3 bg-white shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="user-icon-placeholder">
                                <i class="fas fa-user-tie fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">Sarah Agric</h5>
                            <small class="text-muted">Farm Manager</small>
                        </div>
                    </div>
                    <p class="mb-0">"Reliable delivery and consistent quality. We've been using AgriChem for 5 years with excellent results."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100 rounded-3 bg-white shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="user-icon-placeholder">
                                <i class="fas fa-user-ninja fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">David Cropper</h5>
                            <small class="text-muted">Organic Farmer</small>
                        </div>
                    </div>
                    <p class="mb-0">"Even for organic operations, they have solutions that meet our strict requirements. Highly recommended."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Ready to boost your agricultural productivity?</h2>
                <p class="lead mb-0">Contact our experts today for personalized recommendations.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="contact.php" class="btn btn-light btn-lg px-4 py-2 rounded-pill fw-bold">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>