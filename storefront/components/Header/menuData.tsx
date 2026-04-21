import { Menu } from "@/types/menu";

const menuData: Menu[] = [
  { id: 1, title: "Início", newTab: false, path: "/" },
  { id: 2, title: "Produtos", newTab: false, path: "/produtos" },
  { id: 3, title: "Minha Conta", newTab: false, submenu: [
    { id: 31, title: "Meus Pedidos", newTab: false, path: "/conta/pedidos" },
    { id: 32, title: "Perfil", newTab: false, path: "/conta/perfil" },
    { id: 33, title: "Entrar", newTab: false, path: "/auth/entrar" },
    { id: 34, title: "Cadastrar", newTab: false, path: "/auth/registrar" },
  ]},
  { id: 4, title: "Carrinho", newTab: false, path: "/carrinho" },
];

export default menuData;
