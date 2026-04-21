"use client";

import { catalogApi, type Category, type Product } from "@/lib/api";
import Link from "next/link";
import { useEffect, useState } from "react";
import { useCart } from "@/contexts/CartContext";
import toast from "react-hot-toast";

function fmt(v: string) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function HomeClient() {
  const { addItem } = useCart();
  const [categories, setCategories] = useState<Category[]>([]);
  const [featured, setFeatured] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      catalogApi.categories().then((r) => setCategories(r.data.slice(0, 6))),
      catalogApi.products({ per_page: 8, in_stock: 1 }).then((r) => setFeatured(r.data)),
    ]).finally(() => setLoading(false));
  }, []);

  const handleAdd = async (product: Product) => {
    try {
      await addItem(product.id, 1);
      toast.success(`${product.name} adicionado ao carrinho!`);
    } catch {
      toast.error("Faça login para adicionar ao carrinho.");
    }
  };

  return (
    <main className="pt-20">
      {/* Hero */}
      <section className="bg-gradient-to-br from-primary/10 to-blue-50 py-20 dark:from-primary/5 dark:to-gray-900">
        <div className="mx-auto max-w-c-1280 px-6 text-center">
          <h1 className="mb-4 text-4xl font-bold text-black dark:text-white md:text-6xl">
            Bem-vindo à nossa Loja
          </h1>
          <p className="mb-8 text-lg text-waterloo dark:text-manatee">
            Encontre os melhores produtos com os melhores preços.
          </p>
          <Link
            href="/produtos"
            className="inline-block rounded-full bg-primary px-8 py-3 font-medium text-white transition hover:bg-opacity-90"
          >
            Ver todos os produtos
          </Link>
        </div>
      </section>

      {/* Categories */}
      <section className="py-16">
        <div className="mx-auto max-w-c-1280 px-6">
          <h2 className="mb-8 text-2xl font-bold text-black dark:text-white">Categorias</h2>
          {loading ? (
            <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="h-20 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
              {categories.map((cat) => (
                <Link
                  key={cat.id}
                  href={`/produtos?category_id=${cat.id}`}
                  className="flex items-center justify-center rounded-xl border border-stroke bg-white p-4 text-center text-sm font-medium text-black transition hover:border-primary hover:text-primary dark:border-strokedark dark:bg-blacksection dark:text-white dark:hover:border-primary dark:hover:text-primary"
                >
                  {cat.name}
                </Link>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Featured Products */}
      <section className="bg-gray-50 py-16 dark:bg-blacksection">
        <div className="mx-auto max-w-c-1280 px-6">
          <div className="mb-8 flex items-center justify-between">
            <h2 className="text-2xl font-bold text-black dark:text-white">Produtos em Destaque</h2>
            <Link href="/produtos" className="text-sm text-primary hover:underline">Ver todos →</Link>
          </div>
          {loading ? (
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {Array.from({ length: 8 }).map((_, i) => (
                <div key={i} className="h-64 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {featured.map((product) => (
                <ProductCard key={product.id} product={product} onAdd={handleAdd} />
              ))}
            </div>
          )}
        </div>
      </section>
    </main>
  );
}

function ProductCard({ product, onAdd }: { product: Product; onAdd: (p: Product) => void }) {
  return (
    <div className="group overflow-hidden rounded-xl border border-stroke bg-white transition hover:shadow-md dark:border-strokedark dark:bg-blacksection">
      <Link href={`/produtos/${product.slug}`}>
        <div className="flex h-48 items-center justify-center bg-gray-100 dark:bg-gray-800">
          <span className="text-4xl">🛍️</span>
        </div>
      </Link>
      <div className="p-4">
        <p className="mb-1 text-xs text-waterloo dark:text-manatee">{product.category.name}</p>
        <Link href={`/produtos/${product.slug}`}>
          <h3 className="mb-2 font-medium text-black transition group-hover:text-primary dark:text-white dark:group-hover:text-primary">
            {product.name}
          </h3>
        </Link>
        {product.short_description && (
          <p className="mb-3 text-xs text-waterloo dark:text-manatee line-clamp-2">{product.short_description}</p>
        )}
        <div className="flex items-center justify-between">
          <span className="font-bold text-primary">{fmt(product.price)}</span>
          <button
            onClick={() => onAdd(product)}
            className="rounded-full bg-primary px-3 py-1.5 text-xs font-medium text-white transition hover:bg-opacity-90"
          >
            + Carrinho
          </button>
        </div>
      </div>
    </div>
  );
}
