
const appEnv = import.meta.env.VITE_APP_ENV ?? (import.meta.env.PROD ? 'production' : 'local');

export const useEnv = () => ({
    appEnv,
    isProduction: appEnv === 'production',
    isStaging: appEnv === 'staging',
    isLocal: appEnv === 'local',
    isDev: import.meta.env.DEV,
    envIsOneOf: (...envs) => envs.includes(appEnv),
});
