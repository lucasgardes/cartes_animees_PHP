import retrofit2.Call
import retrofit2.http.*

interface ApiService {
    @POST("api.php?action=login")
    fun login(@Body loginRequest: LoginRequest): Call<LoginResponse>

    @GET("api.php?action=series")
    fun getSeries(): Call<List<Series>>
}
