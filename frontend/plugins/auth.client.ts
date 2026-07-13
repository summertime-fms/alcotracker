export default defineNuxtPlugin(async () => {
  const { checkAuth, authChecked } = useAuth()

  if (!authChecked.value) {
    try {
      await checkAuth()
    } catch {
      // Не авторизован — middleware перенаправит на /login
    }
  }
})
