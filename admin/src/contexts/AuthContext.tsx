"use client";

import { authApi, type AdminUser } from "@/lib/api";
import { useRouter } from "next/navigation";
import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type PropsWithChildren,
} from "react";

interface AuthContextValue {
  user: AdminUser | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue>({
  user: null,
  loading: true,
  login: async () => {},
  logout: async () => {},
});

export function AuthProvider({ children }: PropsWithChildren) {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const fetchProfile = useCallback(async () => {
    const token = localStorage.getItem("admin_token");
    if (!token) {
      setLoading(false);
      return;
    }
    try {
      const { user } = await authApi.profile();
      if (!user.is_admin) {
        localStorage.removeItem("admin_token");
        setUser(null);
      } else {
        setUser(user);
      }
    } catch {
      localStorage.removeItem("admin_token");
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  const login = useCallback(
    async (email: string, password: string) => {
      const { token, user } = await authApi.login(email, password);
      if (!user.is_admin) {
        throw new Error("Acesso restrito a administradores.");
      }
      localStorage.setItem("admin_token", token);
      setUser(user);
      router.push("/");
    },
    [router],
  );

  const logout = useCallback(async () => {
    try {
      await authApi.logout();
    } catch {}
    localStorage.removeItem("admin_token");
    setUser(null);
    router.push("/auth/sign-in");
  }, [router]);

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
