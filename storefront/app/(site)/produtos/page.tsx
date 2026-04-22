"use client";

import { catalogApi, type Product } from "@/lib/api";
import { useCart } from "@/contexts/CartContext";
import Link from "next/link";
import { useSearchParams, useRouter } from "next/navigation";
import { useEffect, useState, Suspense } from "react";
import toast from "react-hot-toast";

function fmt(v: string) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

function ProductsContent() {
  const { addItem } = useCart();
  const searchParams = useSearchParams();
  const router = useRouter();

  const [products, setProducts] = useState<Product[]>([]);
  const [pagination, setPagination] = useState({ total: 0, per_page: 15, current_page: 1, last_page: 1 });
  const [loading, setLoading] = useState(true);

  const search = searchParams.get("search") ?? "";
  const categoryId = searchParams.get("category_id") ?? "";
  const minPrice = searchParams.get("min_price") ?? "";
  const maxPrice = searchParams.get("max_price") ?? "";
  const page = Number(searchParams.get("page") ?? 1);

  const [searchInput, setSearchInput] = useState(search);

  useEffect(() => {
    setLoading(true);
    const params: Record<string, string | number> = { page, per_page: 15 };
    if (search) params.search = search;
    if (categoryId) params.category_id = categoryId;
    if (minPrice) params.min_price = minPrice;
    if (maxPrice) params.max_price = maxPrice;

    catalogApi.products(params)
      .then((r) => { setProducts(r.data); setPagination(r.pagination); })
      .finally(() => setLoading(false));
  }, [search, categoryId, minPrice, maxPrice, page]);

  const goTo = (p: number) => {
    const q = new URLSearchParams(searchParams.toString());
    q.set("page", String(p));
    router.push(`/produtos?${q.toString()}`);
  };

  const handleSearch = () => {
    const q = new URLSearchParams(searchParams.toString());
    q.set("search", searchInput);
    q.set("page", "1");
    router.push(`/produtos?${q.toString()}`);
  };

  const handleAdd = async (product: Product) => {
    try {
      await addItem(product.id, 1, undefined, product);
      toast.success(`${product.name} adicionado!`);
    } catch {
      toast.error("Erro ao adicionar ao carrinho.");
    }
  };

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <h1 className="mb-6 text-3xl font-bold text-black dark:text-white">Produtos</h1>

      {/* Filters */}
      <div className="mb-8 flex flex-wrap gap-3">
        <input
          type="text"
          value={searchInput}
          onChange={(e) => setSearchInput(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && handleSearch()}
          placeholder="Buscar produto..."
          className="rounded-full border border-stroke bg-white px-5 py-2 text-sm outline-none focus:border-primary dark:border-strokedark dark:bg-blacksection dark:text-white"
        />
        <button onClick={handleSearch} className="rounded-full bg-primary px-5 py-2 text-sm text-white hover:bg-opacity-90">
          Buscar
        </button>
        {(search || categoryId) && (
          <button onClick={() => router.push("/produtos")} className="rounded-full border border-stroke px-5 py-2 text-sm hover:border-primary hover:text-primary dark:border-strokedark dark:text-white">
            Limpar filtros ×
          </button>
        )}
      </div>

      {loading ? (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="h-72 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700" />
          ))}
        </div>
      ) : products.length === 0 ? (
        <div className="py-20 text-center text-waterloo dark:text-manatee">
          Nenhum produto encontrado.
        </div>
      ) : (
        <>
          <p className="mb-4 text-sm text-waterloo dark:text-manatee">{pagination.total} produtos encontrados</p>
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {products.map((product) => (
              <div key={product.id} className="group overflow-hidden rounded-xl border border-stroke bg-white transition hover:shadow-md dark:border-strokedark dark:bg-blacksection">
                <Link href={`/produtos/${product.slug}`}>
                  <div className="flex h-48 items-center justify-center bg-gray-100 dark:bg-gray-800">
                    <span className="text-4xl">🛍️</span>
                  </div>
                </Link>
                <div className="p-4">
                  <p className="mb-1 text-xs text-waterloo dark:text-manatee">{product.category.name}</p>
                  <Link href={`/produtos/${product.slug}`}>
                    <h3 className="mb-2 font-medium text-black transition group-hover:text-primary dark:text-white">{product.name}</h3>
                  </Link>
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-primary">{fmt(product.price)}</span>
                    <button onClick={() => handleAdd(product)} className="rounded-full bg-primary px-3 py-1.5 text-xs text-white hover:bg-opacity-90">
                      + Carrinho
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Pagination */}
          <div className="mt-10 flex items-center justify-center gap-3">
            <button disabled={page <= 1} onClick={() => goTo(page - 1)} className="rounded-full border border-stroke px-5 py-2 text-sm disabled:opacity-40 hover:border-primary hover:text-primary dark:border-strokedark dark:text-white">← Anterior</button>
            <span className="text-sm text-waterloo">{page} / {pagination.last_page}</span>
            <button disabled={page >= pagination.last_page} onClick={() => goTo(page + 1)} className="rounded-full border border-stroke px-5 py-2 text-sm disabled:opacity-40 hover:border-primary hover:text-primary dark:border-strokedark dark:text-white">Próxima →</button>
          </div>
        </>
      )}
    </main>
  );
}

export default function ProductsPage() {
  return (
    <Suspense>
      <ProductsContent />
    </Suspense>
  );
}
