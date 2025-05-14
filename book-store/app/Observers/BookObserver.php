<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\BookLog;

class BookObserver
{
    public function created(Book $book)
    {
        BookLog::create([
            'book_id' => $book->id,
            'action' => 'created',
            'details' => 'Book was created with title: ' . $book->title
        ]);
    }

    public function updated(Book $book)
    {
        BookLog::create([
            'book_id' => $book->id,
            'action' => 'updated',
            'details' => 'Book was updated'
        ]);
    }

    public function deleted(Book $book)
    {
        BookLog::create([
            'book_id' => $book->id,
            'action' => 'deleted',
            'details' => 'Book was deleted'
        ]);
    }
}