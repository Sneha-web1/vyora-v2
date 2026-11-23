<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    public function index()
    {
        $data['title'] = 'Skills';
        $data['titleRoute'] = 'Settings / Masters / Skill';

        return view('home.skills.index', $data);
    }

    public function list(Request $request)
    {
        $search = $request->input('search.value');
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);

        $query = Skill::query();

        $recordsTotal = Skill::count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        $skills = $query
            ->orderBy('id', 'desc')
            ->offset($start)
            ->limit($limit)
            ->get();

        $rows = [];

        foreach ($skills as $index => $skill) {
            $rows[] = [
                'id' => $skill->id,
                'DT_RowIndex' => $start + $index + 1,
                'name' => $skill->name,
                'status' => $skill->status,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function create()
    {
        $data['title'] = 'Master / Organisation / Skill Create';

        return view('home.skills.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $slug = Str::slug($request->name);

            $count = Skill::where('slug', 'like', "{$slug}%")->count();

            $finalSlug = $count ? "{$slug}-" . ($count + 1) : $slug;

            Skill::create([
                'name' => $request->name,
                'slug' => $finalSlug,
                'status' => $request->status,
            ]);

            return redirect()
                ->route('settings.skills')
                ->with('success', 'Job Skill created successfully');

        } catch (\Exception $e) {
            Log::error('Skill Create Error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while creating Job Skill');
        }
    }

    public function edit($id)
    {
        $jobSkill = Skill::findOrFail($id);

        return view('home.skills.edit', [
            'title' => 'Master / Organisation / Job Skill Edit',
            'jobSkill' => $jobSkill
        ]);
    }

    public function update(Request $request, $id)
    {
        $jobSkill = Skill::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $jobSkill->id,
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $slug = Str::slug($request->name);

            $count = Skill::where('slug', 'like', "{$slug}%")
                ->where('id', '!=', $jobSkill->id)
                ->count();

            $finalSlug = $count ? "{$slug}-" . ($count + 1) : $slug;

            $jobSkill->update([
                'name' => $request->name,
                'slug' => $finalSlug,
                'status' => $request->status,
            ]);

            return redirect()
                ->route('settings.skills')
                ->with('success', 'Job Skill updated successfully!');

        } catch (\Exception $e) {
            Log::error('Skill Update Error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating Job Skill');
        }
    }

    public function destroy($id)
    {
        try {
            $jobSkill = Skill::find($id);

            if (!$jobSkill) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job Skill not found'
                ], 404);
            }

            $jobSkill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job Skill deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Skill Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
