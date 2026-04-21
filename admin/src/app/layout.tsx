import "@/css/satoshi.css";
import "@/css/style.css";

import { AdminLayout } from "@/components/Layouts/AdminLayout";
import type { Metadata } from "next";
import NextTopLoader from "nextjs-toploader";
import type { PropsWithChildren } from "react";
import { Providers } from "./providers";

import "flatpickr/dist/flatpickr.min.css";
import "jsvectormap/dist/jsvectormap.css";

export const metadata: Metadata = {
  title: {
    template: "%s | Admin",
    default: "Painel Administrativo",
  },
  description: "Painel administrativo do e-commerce.",
};

export default function RootLayout({ children }: PropsWithChildren) {
  return (
    <html lang="pt-BR" suppressHydrationWarning>
      <body>
        <Providers>
          <NextTopLoader color="#5750F1" showSpinner={false} />
          <AdminLayout>{children}</AdminLayout>
        </Providers>
      </body>
    </html>
  );
}
