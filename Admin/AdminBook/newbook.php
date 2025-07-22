<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $price = $_POST['price'] ?? 0;
    $hasHardCopy = isset($_POST['hasHardCopy']) ? 1 : 0;
    $hasCD = isset($_POST['hasCD']) ? 1 : 0;
    $stock = $_POST['stock'];
    $pdfPath = '';
    $image_name = '';

    // Handle PDF Upload
    if (!empty($_FILES['pdfPath']['name'])) {
        $pdf_name = basename($_FILES['pdfPath']['name']);
        $pdf_tmp = $_FILES['pdfPath']['tmp_name'];
        $pdf_folder = "../../uploads/PDFs/" . $pdf_name;
        if (!is_dir("../../uploads/PDFs/")) {
            mkdir("../../uploads/PDFs/", 0777, true);
        }
        if (move_uploaded_file($pdf_tmp, $pdf_folder)) {
            $pdfPath = $pdf_name;
        }
    }

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $image_name = basename($_FILES['image']['name']);
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_folder = "../../uploads/Images/" . $image_name;
        if (!is_dir("../../uploads/Images/")) {
            mkdir("../../uploads/Images/", 0777, true);
        }
        move_uploaded_file($image_tmp, $image_folder);
    }

    // Insert into books table
    $sql = "INSERT INTO books (Title, Author, Category, Price, PDFPath, HasHardCopy, HasCD, Stock, image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssdsiiss", $title, $author, $category, $price, $pdfPath, $hasHardCopy, $hasCD, $stock, $image_name);
        if ($stmt->execute()) {
            header("Location: admin.books.php");
            exit();
        } else {
            echo "Database Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Prepare failed: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book | E - Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#020617',
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                            600: '#475569',
                            500: '#64748b',
                            400: '#94a3b8',
                            300: '#cbd5e1'
                        },
                        emerald: {
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    },
                    borderRadius: {
                        none: "0px",
                        sm: "4px",
                        DEFAULT: "8px",
                        md: "12px",
                        lg: "16px",
                        xl: "20px",
                        "2xl": "24px",
                        "3xl": "32px",
                        full: "9999px",
                        button: "8px",
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        #sidebar.collapsed ~ #main-content {
            margin-left: 4rem; /* Matches collapsed sidebar width (w-16) */
        }
        #sidebar:not(.collapsed) ~ #main-content {
            margin-left: 16rem; /* Matches expanded sidebar width (w-64) */
        }
        .scroll-animate {
            opacity: 0;
            transform: translateY(20px);
            transition: all 1s ease-out;
        }
        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .parallax-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        .bg-blur {
            filter: blur(3rem);
        }
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.3s ease;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .mobile-menu.open {
            max-height: 500px;
        }
        #sidebar.collapsed ~ #main-content {
            margin-left: 4rem; /* Matches collapsed sidebar width (w-16) */
        }
        #sidebar:not(.collapsed) ~ #main-content {
            margin-left: 16rem; /* Matches expanded sidebar width (w-64) */
        }
        input:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3); /* emerald-500/30 */
            border-color: #10b981; /* emerald-500 */
        }
        input[type="checkbox"]:checked {
            background-color: #10b981; /* emerald-500 */
            border-color: #10b981;
        }
    </style>
</head>
<body class="bg-slate-950 text-white font-inter">
    <div class="flex min-h-screen">
        <?php include "../Components/sider.php"; ?>
        <!-- Main Content -->
        <main id="main-content" class="main-content min-h-screen w-full transition-all duration-300">
            <!-- Header -->
            <!-- Page Content -->
            <div class="pt-16 p-6">
                <div class="max-w-5xl mx-auto">
                    <h1 class="text-4xl font-black text-white mb-6 scroll-animate">Add New Book</h1>
                    <?php if (isset($error)): ?>
                        <div class="mb-6 p-4 bg-red-500/20 text-red-400 border border-red-500/30 rounded-xl scroll-animate">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6 backdrop-blur-2xl bg-slate-800/30 border border-slate-700/50 shadow-2xl hover:shadow-emerald-500/20 transition-all duration-500 p-6 rounded-2xl scroll-animate">
                        <div class="absolute inset-0 bg-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                        <div class="relative">
                            <label class="block mb-1 font-medium text-slate-300">Title</label>
                            <input type="text" name="title" required class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-md text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300" placeholder="Enter book title" />
                        </div>
                        <div class="relative">
                            <label class="block mb-1 font-medium text-slate-300">Author</label>
                            <input type="text" name="author" class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-md text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300" placeholder="Enter author name" />
                        </div>
                        <div class="relative md:col-span-2">
                            <label class="block mb-1 font-medium text-slate-300">Category</label>
                            <select name="category" class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300" required>
                                <option value="" class="bg-slate-700">Select a Category</option>
                                <?php
                                $catResult = $conn->query("SELECT CategoryID, CategoryName FROM categories");
                                if ($catResult && $catResult->num_rows > 0) {
                                    while ($cat = $catResult->fetch_assoc()) {
                                        echo "<option value='" . $cat['CategoryID'] . "' class='bg-slate-700'>" . htmlspecialchars($cat['CategoryName']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="relative">
                            <label class="block mb-2 text-sm font-semibold text-slate-300">Book Cover Image</label>
                            <input type="file" name="image"
                                class="block w-full text-sm text-slate-300 bg-slate-800/50 border border-slate-600/50 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 transition-all duration-300" />
                        </div>
                        <div class="relative">
                            <label class="block mb-2 text-sm font-semibold text-slate-300">PDF File</label>
                            <input type="file" name="pdfPath"
                                class="block w-full text-sm text-slate-300 bg-slate-800/50 border border-slate-600/50 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 transition-all duration-300" />
                        </div>
                        <div class="relative">
                            <label class="block mb-1 font-medium text-slate-300">Price</label>
                            <input type="number" step="0.01" name="price" class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-md text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300" placeholder="Enter price" />
                        </div>
                        <div class="relative">
                            <label class="block mb-1 font-medium text-slate-300">Stock</label>
                            <input type="number" name="stock" min="0" required class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-md text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300" placeholder="Enter stock quantity" />
                        </div>
                        <div class="flex items-center gap-6 mb-4">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="hasHardCopy" class="peer hidden" />
                                <div class="w-5 h-5 flex items-center justify-center rounded-full border border-slate-500 bg-slate-800 
                                            peer-checked:bg-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-400 
                                            transition-all duration-300 shadow-sm">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" 
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-slate-300 text-sm group-hover:text-emerald-400 transition-colors font-medium">
                                    Has Hard Copy
                                </span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="hasCD" class="peer hidden" />
                                <div class="w-5 h-5 flex items-center justify-center rounded-full border border-slate-500 bg-slate-800 
                                            peer-checked:bg-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-400 
                                            transition-all duration-300 shadow-sm">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" 
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-slate-300 text-sm group-hover:text-emerald-400 transition-colors font-medium">
                                    Has CD
                                </span>
                            </label>
                        </div>

                        <div class="md:col-span-2 text-center relative">
                            <input type="submit" value="Add Book" class="px-6 py-2 bg-emerald-500 text-white rounded-button font-medium hover:bg-emerald-600 transition-all duration-300 cursor-pointer">
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });

        // Parallax background effect
        let scrollY = 0;
        
        function updateParallax() {
            scrollY = window.scrollY;
            
            const bg1 = document.getElementById('bg1');
            const bg2 = document.getElementById('bg2');
            const bg3 = document.getElementById('bg3');
            
            if (bg1) bg1.style.transform = `translateY(${scrollY * 0.1}px)`;
            if (bg2) bg2.style.transform = `translateY(${scrollY * -0.15}px)`;
            if (bg3) bg3.style.transform = `translateY(${scrollY * 0.05}px)`;
        }

        window.addEventListener('scroll', updateParallax);
        updateParallax();

        // Ensure main content margin is set on page load
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");
            if (sidebar.classList.contains("collapsed")) {
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
            } else {
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
            }
        });
    </script>
</body>
</html>