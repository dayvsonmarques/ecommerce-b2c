import { Metadata } from "next";
import HomeClient from "./_home/HomeClient";

export const metadata: Metadata = { title: "Loja | Início" };

export default function Home() {
  return <HomeClient />;
}
