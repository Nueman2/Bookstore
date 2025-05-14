<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">📚 Welcome to the Book Store</h1>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Add New Book</div>
            <div class="card-body">
                <form method="post" id="new-book-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required placeholder="Enter book title">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" name="author" id="author" class="form-control" required placeholder="Enter author name">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="genre" class="form-label">Genre</label>
                            <input type="text" name="genre" id="genre" class="form-control" required placeholder="Enter book genre">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="year" class="form-label">Published Year</label>
                            <input type="number" name="year" id="year" class="form-control" required placeholder="Enter published year">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="cover">Cover Photo</label>
                            <input type="file" name="cover" id="cover" accept="image/*" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Add Book</button>
                </form>
            </div>
        </div>

        <div class="card mb-4" id="edit-form" style="display: none;">
            <div class="card-header bg-warning">Edit Book</div>
            <div class="card-body">
                <form id="edit-book-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <input type="hidden" id="edit-id" name="id">

                        <!-- Current Cover Display -->
                        <div class="mb-3 col-12">
                            <label class="form-label">Current Cover</label>
                            <div id="current-cover-container" class="mb-2">
                                <img id="current-cover" src="" class="img-thumbnail" style="max-width: 150px; display: none;">
                                <div id="no-cover-message" class="text-muted">No cover image</div>
                            </div>
                        </div>

                        <!-- New Cover Upload -->
                        <div class="mb-3 col-12">
                            <label for="edit-cover" class="form-label">Update Cover Image</label>
                            <input type="file" class="form-control" id="edit-cover" name="cover" accept="image/*">
                            <div class="form-text">Max 2MB. Accepted formats: JPG, PNG, GIF</div>

                            <!-- New Cover Preview -->
                            <div id="edit-cover-preview" class="mt-2" style="display: none;">
                                <p class="small mb-1">New cover preview:</p>
                                <img src="" class="img-thumbnail" style="max-width: 150px;">
                            </div>
                        </div>

                        <!-- Book Details -->
                        <div class="mb-3 col-md-6">
                            <label for="edit-title" class="form-label">Title*</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="edit-author" class="form-label">Author*</label>
                            <input type="text" id="edit-author" name="author" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="edit-genre" class="form-label">Genre*</label>
                            <input type="text" id="edit-genre" name="genre" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="edit-year" class="form-label">Published Year*</label>
                            <input type="number" id="edit-year" name="year" class="form-control" min="1800" max="<?= date('Y') + 1 ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="cancel-edit" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="submit-text">Update Book</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <h2 class="mb-3">📖 Book List</h2>

        <div id="books-container" class="container-fluid py-4">
            <!-- Books will be loaded here dynamically -->
        </div>

        <footer class="mt-5 text-center text-muted">
            <p>&copy; {{ date('Y') }} Book Store. All rights reserved.</p>
        </footer>

    </div>

    <script>
        $(document).ready(() => {
            const baseUrl = '/api/books';
            const $booksContainer = $("#books-container");
            const $editForm = $('#edit-form');
            const $newBookForm = $('#new-book-form');
            const $editBookForm = $('#edit-book-form');

            // Show loading indicator
            function showLoading() {
                $booksContainer.html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            }

            // Load books with card layout
            function loadBooks() {
                showLoading();
                axios.get(baseUrl)
                    .then(response => {
                        $booksContainer.empty();

                        const booksData = Array.isArray(response.data) ? response.data : [];

                        if (booksData.length === 0) {
                            $booksContainer.html('<div class="col-12 text-center py-5"><h4>No books found in our collection</h4></div>');
                            return;
                        }

                        const booksGrid = $('<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4"></div>');

                        booksData.forEach(book => {
                            const coverUrl = book.cover_photo ? `/storage/${book.cover_photo}` : '/placeholder-book.jpg';
                            const bookCard = `
                            <div class="col" data-id="${book.id}">
                                <div class="card h-100 book-card shadow-sm">
                                    <div class="card-img-top-container position-relative" style="height: 300px; overflow: hidden;">
                                        <img src="${coverUrl}" class="card-img-top h-100 object-fit-cover" alt="${book.title}">
                                        <div class="book-badge">${book.genre}</div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">${book.title}</h5>
                                        <p class="card-text text-muted">${book.author}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">${book.published_year}</small>
                                            <div class="btn-group">
                                                <button class="edit-btn btn btn-sm btn-outline-primary">Edit</button>
                                                <button class="delete-btn btn btn-sm btn-outline-danger">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                            booksGrid.append(bookCard);
                        });

                        $booksContainer.append(booksGrid);
                    })
                    .catch(error => {
                        $booksContainer.html(`
                        <div class="col-12 text-center py-5">
                            <div class="alert alert-danger">
                                <h4>Error loading books</h4>
                                <p>${error.message}</p>
                                <button class="btn btn-secondary mt-3" onclick="loadBooks()">Retry</button>
                            </div>
                        </div>
                    `);
                        console.error("Error fetching books:", error);
                    });
            }

            // Add new book
            $newBookForm.on('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);

                axios.post(baseUrl, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(response => {
                        alert(response.data.message);
                        $newBookForm[0].reset();
                        loadBooks();
                    })
                    .catch(error => {
                        const errorMsg = error.response?.data?.message || error.message;
                        alert("Error adding book: " + errorMsg);
                    });
            });

            // Edit book - updated for card layout
            $booksContainer.on('click', '.edit-btn', function(e) {
                e.stopPropagation();
                const card = $(this).closest('[data-id]');
                const bookId = card.data('id');

                // Get book data from card
                const bookData = {
                    title: card.find('.card-title').text(),
                    author: card.find('.card-text').text(),
                    genre: card.find('.book-badge').text(),
                    year: card.find('small').text(),
                    cover: card.find('img').attr('src')
                };

                // Populate edit form
                $('#edit-id').val(bookId);
                $('#edit-title').val(bookData.title);
                $('#edit-author').val(bookData.author);
                $('#edit-genre').val(bookData.genre);
                $('#edit-year').val(bookData.year);

                // Show current cover
                $('#current-cover').attr('src', bookData.cover || '/placeholder-book.jpg')
                    .toggle(!!bookData.cover);
                $('#no-cover-message').toggle(!bookData.cover);

                $editForm.show();
            });

            // Cancel edit
            $('#cancel-edit').on('click', () => {
                $editForm.hide();
                $editBookForm[0].reset();
                $('#edit-cover-preview').hide();
            });

            // Update book
            $editBookForm.on('submit', (e) => {
                e.preventDefault();
                const id = $('#edit-id').val();
                const formData = new FormData(e.target);

                // Show loading state
                const submitBtn = $editBookForm.find('[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

                axios.post(`${baseUrl}/${id}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        params: {
                            _method: 'PUT'
                        }
                    })
                    .then(response => {
                        alert(response.data.message);
                        $editForm.hide();
                        $editBookForm[0].reset();
                        loadBooks();
                    })
                    .catch(error => {
                        const errorMsg = error.response?.data?.message || error.message;
                        alert("Error updating book: " + errorMsg);
                    })
                    .finally(() => {
                        submitBtn.prop('disabled', false).text('Update Book');
                    });
            });

            // Delete book - updated for card layout
            $booksContainer.on('click', '.delete-btn', function(e) {
                e.stopPropagation();
                if (confirm('Are you sure you want to delete this book?')) {
                    const card = $(this).closest('[data-id]');
                    const id = card.data('id');

                    // Add loading state to the card
                    card.html('<div class="card-body text-center py-4"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Deleting...</span></div></div>');

                    axios.delete(`${baseUrl}/${id}`)
                        .then(response => {
                            alert(response.data.message);
                            loadBooks();
                        })
                        .catch(error => {
                            const errorMsg = error.response?.data?.message || error.message;
                            alert("Error deleting book: " + errorMsg);
                            loadBooks();
                        });
                }
            });

            // Preview new cover image
            $('#edit-cover').on('change', function() {
                const file = this.files[0];
                const preview = $('#edit-cover-preview');

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.find('img').attr('src', e.target.result);
                        preview.show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.hide();
                }
            });

            // Initial load
            loadBooks();
        });
    </script>
</body>

</html>