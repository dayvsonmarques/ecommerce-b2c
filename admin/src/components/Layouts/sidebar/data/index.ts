import type React from "react";
import * as Icons from "../icons";

type SubItem = { title: string; url: string };
type NavItem = { title: string; url: string; icon: React.ComponentType<React.SVGProps<SVGSVGElement>>; items: SubItem[] };
type NavSection = { label: string; items: NavItem[] };

export const NAV_DATA: NavSection[] = [
  {
    label: "PAINEL",
    items: [
      {
        title: "Dashboard",
        url: "/",
        icon: Icons.HomeIcon,
        items: [],
      },
    ],
  },
  {
    label: "CATÁLOGO",
    items: [
      {
        title: "Produtos",
        url: "/products",
        icon: Icons.Table,
        items: [],
      },
    ],
  },
  {
    label: "VENDAS",
    items: [
      {
        title: "Pedidos",
        url: "/orders",
        icon: Icons.PieChart,
        items: [],
      },
    ],
  },
  {
    label: "CLIENTES",
    items: [
      {
        title: "Usuários",
        url: "/users",
        icon: Icons.User,
        items: [],
      },
    ],
  },
  {
    label: "CMS",
    items: [
      {
        title: "Páginas",
        url: "/cms/pages",
        icon: Icons.DocumentIcon,
        items: [],
      },
    ],
  },
  {
    label: "CONFIGURAÇÕES",
    items: [
      {
        title: "Loja",
        url: "/configuracoes/loja",
        icon: Icons.SettingsIcon,
        items: [],
      },
    ],
  },
];
