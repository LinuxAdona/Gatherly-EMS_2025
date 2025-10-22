import { useState } from "react";

interface Props {
  label: string;
  type?: "password" | "text";
}

const InputForm = ({ label, type }: Props) => {
  const [showPassword, setShowPassword] = useState(false);

  const isPassword = type === "password" || label.toLowerCase() === "password";
  const inputType = isPassword ? (showPassword ? "text" : "password") : "text";

  return (
    <>
      {label.toLowerCase() === "password" ? (
        <div className="flex items-center justify-between mt-2">
          <label className="text-sm font-medium">{label}</label>
          <a href="#" className="ml-auto text-sm text-cyan-800 hover:underline">
            Forgot password?
          </a>
        </div>
      ) : (
        <label className="mt-2 text-sm font-medium">{label}</label>
      )}

      <div className="relative mt-1">
        <input
          type={inputType}
          className="w-full p-2 pr-10 bg-white border border-gray-300 rounded-md text-medium focus:outline-none focus:ring-2 focus:ring-blue-500"
          aria-label={label}
        />

        {isPassword && (
          <button
            type="button"
            onClick={() => setShowPassword((s) => !s)}
            className="absolute p-1 text-gray-600 -translate-y-1/2 right-2 top-1/2 hover:text-gray-900"
            aria-pressed={showPassword}
            aria-label={showPassword ? "Hide password" : "Show password"}
          >
            {showPassword ? (
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-5 0-9.27-3.11-11-7 1.05-2.02 2.6-3.73 4.45-4.84M6.1 6.1A9.953 9.953 0 0 1 12 5c5 0 9.27 3.11 11 7-1.02 1.96-2.57 3.65-4.4 4.76M3 3l18 18"
                />
              </svg>
            ) : (
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"
                />
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.275 4.057-5.065 7-9.543 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
            )}
          </button>
        )}
      </div>
    </>
  );
};

export default InputForm;
