"use client";

import { AuthProvider } from "@/contexts/AuthContext";
import { SidebarProvider } from "@/components/Layouts/sidebar/sidebar-context";
import { ThemeProvider } from "next-themes";

export function Providers({ children }: { children: React.ReactNode }) {
  return (
    <ThemeProvider defaultTheme="light" attribute="class">
      <SidebarProvider>
        <AuthProvider>{children}</AuthProvider>
      </SidebarProvider>
    </ThemeProvider>
  );
}
