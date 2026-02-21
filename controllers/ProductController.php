<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../includes/functions.php';

class ProductController {
    private $productModel;
    private $reviewModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->reviewModel = new Review();
    }

    public function home() {
        // Obtener productos agrupados por material para los carruseles
        $allProducts = $this->productModel->getAll();
        $productsByMaterial = [];
        
        foreach ($allProducts as $product) {
            $material = $product['material'] ?? 'Sin material';
            if (!isset($productsByMaterial[$material])) {
                $productsByMaterial[$material] = [];
            }
            $productsByMaterial[$material][] = $product;
        }
        
        include __DIR__ . '/../views/home.php';
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $material = $_GET['material'] ?? null;
        $priceRange = $_GET['price'] ?? null;
        
        if ($search || $material || $priceRange) {
            $products = $this->productModel->search($search, $material, $priceRange);
        } else {
            $products = $this->productModel->getAll();
        }
        
        $categories = $this->productModel->getCategories();
        
        include __DIR__ . '/../views/products/index.php';
    }

    public function show() {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            setFlashMessage('Producto no encontrado', 'error');
            redirect('/My3DStore/?action=products');
        }
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            setFlashMessage('Producto no encontrado', 'error');
            redirect('/My3DStore/?action=products');
        }
        
        $reviews = $this->reviewModel->getByProductId($id);
        $ratingInfo = $this->reviewModel->getAverageRating($id);
        
        $canReview = false;
        if (isLoggedIn()) {
            $canReview = !$this->reviewModel->hasUserReviewed($id, $_SESSION['user_id']);
        }
        
        include __DIR__ . '/../views/products/show.php';
    }

    public function createReview() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id'] ?? 0);
            $userId = $_SESSION['user_id'];
            $rating = intval($_POST['rating'] ?? 0);
            $comment = sanitize($_POST['comment'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                setFlashMessage('La calificación debe estar entre 1 y 5', 'error');
                redirect("/My3DStore/?action=product&id=$productId");
            }
            
            if ($this->reviewModel->hasUserReviewed($productId, $userId)) {
                setFlashMessage('Ya has dejado una reseña para este producto', 'error');
                redirect("/My3DStore/?action=product&id=$productId");
            }
            
            if ($this->reviewModel->create($productId, $userId, $rating, $comment)) {
                setFlashMessage('Reseña publicada correctamente', 'success');
            } else {
                setFlashMessage('Error al publicar la reseña', 'error');
            }
            
                redirect("/My3DStore/?action=product&id=$productId");
        }
    }
}

