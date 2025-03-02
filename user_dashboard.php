<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Website</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f4f4;
            color: #333;
        }
        nav {
            background: #333;
            padding: 1rem;
        }
        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
        }
        header {
            text-align: center;
            padding: 2rem;
            background: #0073e6;
            color: white;
        }
        main {
            display: flex;
            padding: 2rem;
            gap: 20px;
        }
        .categories {
            width: 20%;
            background: white;
            padding: 1rem;
            border-radius: 5px;
        }
        .categories ul {
            list-style: none;
        }
        .book-list {
            width: 80%;
        }
        .category {
            margin-bottom: 2rem;
        }
        .book-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }
        .book {
            background: white;
            padding: 1rem;
            border-radius: 5px;
            min-width: 150px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .book img {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .book h4, .book p {
            margin: 5px 0;
        }
        .download-btn, .discussion-btn {
            display: inline-block;
            padding: 5px 10px;
            margin-top: 10px;
            background: #0073e6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .trending {
            padding: 2rem;
            background: white;
            text-align: center;
        }
        .trending-books {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        footer {
            text-align: center;
            padding: 1rem;
            background: #333;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
          
            <!-- <li><a href="discussion.php">Discussion</a></li> -->
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="profile.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <header>
        <h1>Featured & Trending Books</h1>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Book List -->
        <section class="book-list">
            <h2>Books</h2>
            <!-- Dynamically Loaded Books Will Appear Here -->
        </section>
    </main>

    <!-- Trending Books Section -->
    <section class="trending">
        <h2>Trending Books</h2>
        <div class="trending-books">
            <!-- Dynamically Loaded Trending Books Will Appear Here -->
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Book Website</p>
    </footer>

    <script>
        // Fetch books data from the backend
        fetch('fetch_books.php')
            .then(response => response.json())
            .then(books => {
                const bookList = document.querySelector('.book-list');
                const trendingBooks = document.querySelector('.trending-books');

                // Group books by category
                const categories = {};
                books.forEach(book => {
                    if (!categories[book.category]) {
                        categories[book.category] = [];
                    }
                    categories[book.category].push(book);
                });

                // Display books by category
                for (const category in categories) {
                    const categorySection = document.createElement('div');
                    categorySection.classList.add('category');
                    categorySection.innerHTML = `<h3>${category}</h3>`;
                    
                    const bookContainer = document.createElement('div');
                    bookContainer.classList.add('book-container');

                    categories[category].forEach(book => {
                        const bookElement = document.createElement('div');
                        bookElement.classList.add('book');
                        bookElement.innerHTML = `
                            <img src="${book.image_url}" alt="${book.title}">
                            <h4>${book.title}</h4>
                            <p>Author: ${book.author}</p>
                            <a href="${book.download_link}" class="download-btn" target="_blank">Download</a>
                            <a href="discussion.php?book_id=${book.id}" class="discussion-btn" target="_blank">Discussion</a>
                        `;
                        bookContainer.appendChild(bookElement);
                    });

                    categorySection.appendChild(bookContainer);
                    bookList.appendChild(categorySection);

                    if (category === 'Trending') {
                        trendingBooks.appendChild(bookContainer);
                    }
                }
            })
            .catch(error => console.error('Error fetching books:', error));
    </script>
</body>
</html>
