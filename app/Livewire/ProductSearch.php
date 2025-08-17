<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

class ProductSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryId = '';
    public $inStock = false;
    public $featured = false;
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => ''],
        'inStock' => ['except' => false],
        'featured' => ['except' => false],
        'sortBy' => ['except' => 'name'],
        'perPage' => ['except' => 12],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Product::query()
            ->with(['category']);

        // Filtry
        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->inStock) {
            $query->inStock();
        }

        if ($this->featured) {
            $query->featured();
        }

        // Sortowanie
        $query->orderBy($this->sortBy, $this->sortDirection);

        $products = $query->paginate($this->perPage);
        $categories = Category::active()->orderBy('name')->get();

        return view('livewire.product-search', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock_quantity < 1) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Produkt niedostępny'
            ]);
            return;
        }

        // Tutaj logika dodawania do koszyka
        session()->push('cart', [
            'product_id' => $productId,
            'quantity' => 1,
            'added_at' => now(),
        ]);

        $this->emit('cartUpdated');
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Produkt dodany do koszyka'
        ]);
    }
}