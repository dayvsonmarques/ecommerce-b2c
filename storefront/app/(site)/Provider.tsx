"use client";

import Footer from "@/components/Footer";
import Header from "@/components/Header";
import ScrollToTop from "@/components/ScrollToTop";
import { AuthProvider } from "@/contexts/AuthContext";
import { CartProvider } from "@/contexts/CartContext";
import { ThemeProvider } from "next-themes";
import ToasterContext from "../context/ToastContext";

export default function ClientLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <ThemeProvider enableSystem={false} attribute="class" defaultTheme="light">
            <AuthProvider>
                <CartProvider>
                    <Header />
                    <ToasterContext />
                    {children}
                    <Footer />
                    <ScrollToTop />
                </CartProvider>
            </AuthProvider>
        </ThemeProvider>
    );
}
