import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Allow sign-in page freely
  if (pathname.startsWith("/auth/")) return NextResponse.next();

  // The actual token check happens client-side in AuthContext.
  // Middleware here just ensures the page exists and returns next.
  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|images|css).*)"],
};
