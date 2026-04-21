"use client";

import { authApi, type User, type RegisterData } from "@/lib/api";
import { useRouter } from "next/navigation";
import { createContext, useCallback, useContext, useEffect, useState, type PropsWithChildren } from "react";

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue>({
  user: null, loading: true,
  login: async () => {}, register: async () => {}, logout: async () => {},
});

export function AuthProvider({ children }: PropsWithChildren) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const token = localStorage.getItem("token");
    if (!token) { setLoading(false); return; }
    authApi.profile()
      .then(({ user }) => setUser(user))
      .catch(() => localStorage.removeItem("token"))
      .finally(() => setLoading(false));
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const { token, user } = await authApi.login(email, password);
    localStorage.setItem("token", token);
    setUser(user);
    router.push("/conta/pedidos");
  }, [router]);

  const register = useCallback(async (data: RegisterData) => {
    const { token, user } = await authApi.register(data);
    localStorage.setItem("token", token);
    setUser(user);
    router.push("/");
  }, [router]);

  const logout = useCallback(async () => {
    try { await authApi.logout(); } catch {}
    localStorage.removeItem("token");
    setUser(null);
    router.push("/");
  }, [router]);

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() { return useContext(AuthContext); }
