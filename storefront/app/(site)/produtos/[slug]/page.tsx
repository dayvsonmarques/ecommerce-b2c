"use client";

import { catalogApi, type Product, type Sku } from "@/lib/api";
import { useCart } from "@/contexts/CartContext";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import toast from "react-hot-toast";

function fmt(v: string) {
  return Number(v).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export default function ProductDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { addItem } = useCart();

  const [product, setProduct] = useState<Product | null>(null);
  const [related, setRelated] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedSku, setSelectedSku] = useState<Sku | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [adding, setAdding] = useState(false);

  useEffect(() => {
    const slug = params.slug as string;
    Promise.all([
      catalogApi.product(slug).then((r) => { setProduct(r.data); }),
      catalogApi.related(slug).then((r) => setRelated(r.data.slice(0, 4))),
    ]).finally(() => setLoading(false));
  }, [params.slug]);

  const handleAdd = async () => {
    if (!product) return;
    setAdding(true);
    try {
      await addItem(product.id, quantity, selectedSku?.id);
      toast.success("Adicionado ao carrinho!");
    } catch {
      toast.error("Faça login para adicionar ao carrinho.");
    } finally {
      setAdding(false);
    }
  };

  if (loading) {
    return (
      <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
        <div className="grid grid-cols-1 gap-12 md:grid-cols-2">
          <div className="h-96 animate-pulse rounded-xl bg-gray-200 dark:bg-gray-700" />
          <div className="space-y-4">
            {Array.from({ length: 5 }).map((_, i) => <div key={i} className="h-8 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />)}
          </div>
        </div>
      </main>
    );
  }

  if (!product) return <main className="pt-28 text-center text-waterloo">Produto não encontrado.</main>;

  const price = selectedSku ? selectedSku.price : product.price;
  const stock = selectedSku ? selectedSku.quantity : product.quantity;

  return (
    <main className="mx-auto max-w-c-1280 px-6 pb-20 pt-28">
      <nav className="mb-6 text-sm text-waterloo dark:text-manatee">
        <Link href="/" className="hover:text-primary">Início</Link>
        {" / "}
        <Link href="/produtos" className="hover:text-primary">Produtos</Link>
        {" / "}
        <span className="text-black dark:text-white">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 gap-12 md:grid-cols-2">
        {/* Image */}
        <div className="flex h-96 items-center justify-center rounded-2xl border border-stroke bg-gray-50 dark:border-strokedark dark:bg-blacksection">
          <span className="text-8xl">🛍️</span>
        </div>

        {/* Info */}
        <div>
          <p className="mb-2 text-sm text-waterloo dark:text-manatee">{product.category.name}</p>
          <h1 className="mb-3 text-3xl font-bold text-black dark:text-white">{product.name}</h1>

          {product.short_description && (
            <p className="mb-4 text-waterloo dark:text-manatee">{product.short_description}</p>
          )}

          <p className="mb-6 text-3xl font-bold text-primary">{fmt(price)}</p>

          {/* SKU selector */}
          {product.skus && product.skus.length > 0 && (
            <div className="mb-6">
              <p className="mb-2 text-sm font-medium text-black dark:text-white">Variação</p>
              <div className="flex flex-wrap gap-2">
                {product.skus.filter((s) => s.is_active).map((sku) => (
                  <button
                    key={sku.id}
                    onClick={() => setSelectedSku(sku.id === selectedSku?.id ? null : sku)}
                    className={`rounded-full border px-4 py-1.5 text-sm transition ${selectedSku?.id === sku.id ? "border-primary bg-primary text-white" : "border-stroke hover:border-primary hover:text-primary dark:border-strokedark dark:text-white"}`}
                  >
                    {sku.sku}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Quantity */}
          <div className="mb-6 flex items-center gap-4">
            <p className="text-sm font-medium text-black dark:text-white">Quantidade</p>
            <div className="flex items-center rounded-full border border-stroke dark:border-strokedark">
              <button onClick={() => setQuantity((q) => Math.max(1, q - 1))} className="px-4 py-2 text-lg hover:text-primary">−</button>
              <span className="w-8 text-center text-sm font-medium text-black dark:text-white">{quantity}</span>
              <button onClick={() => setQuantity((q) => Math.min(stock, q + 1))} className="px-4 py-2 text-lg hover:text-primary">+</button>
            </div>
            <span className="text-sm text-waterloo dark:text-manatee">{stock} em estoque</span>
          </div>

          <div className="flex gap-3">
            <button
              onClick={handleAdd}
              disabled={adding || stock === 0}
              className="flex-1 rounded-full bg-primary py-3 font-medium text-white transition hover:bg-opacity-90 disabled:opacity-60"
            >
              {adding ? "Adicionando..." : stock === 0 ? "Sem estoque" : "Adicionar ao Carrinho"}
            </button>
            <button onClick={() => router.push("/carrinho")} className="rounded-full border border-primary px-6 py-3 font-medium text-primary transition hover:bg-primary hover:text-white dark:border-primary">
              Carrinho
            </button>
          </div>

          {/* Description */}
          <div className="mt-8 border-t border-stroke pt-6 dark:border-strokedark">
            <h2 className="mb-3 font-semibold text-black dark:text-white">Descrição</h2>
            <p className="text-sm leading-relaxed text-waterloo dark:text-manatee">{product.description}</p>
          </div>
        </div>
      </div>

      {/* Related */}
      {related.length > 0 && (
        <section className="mt-16">
          <h2 className="mb-6 text-xl font-bold text-black dark:text-white">Produtos Relacionados</h2>
          <div className="grid grid-cols-2 gap-6 md:grid-cols-4">
            {related.map((p) => (
              <Link key={p.id} href={`/produtos/${p.slug}`} className="group rounded-xl border border-stroke p-4 transition hover:border-primary dark:border-strokedark dark:bg-blacksection">
                <div className="mb-3 flex h-32 items-center justify-center rounded-lg bg-gray-100 text-3xl dark:bg-gray-800">🛍️</div>
                <p className="font-medium text-black transition group-hover:text-primary dark:text-white">{p.name}</p>
                <p className="mt-1 font-bold text-primary">{fmt(p.price)}</p>
              </Link>
            ))}
          </div>
        </section>
      )}
    </main>
  );
}
